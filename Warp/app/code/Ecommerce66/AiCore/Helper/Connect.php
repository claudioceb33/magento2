<?php
declare(strict_types=1);

namespace Ecommerce66\AiCore\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use InvalidArgumentException;

class Connect extends AbstractHelper
{
    private CurlFactory $curlFactory;
    private Json $json;
    private LoggerInterface $logger;
    private Data $config;
    private StoreManagerInterface $storeManager;

    /**
     * Connect constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param CurlFactory                           $curlFactory
     * @param Json                                  $json
     * @param LoggerInterface                       $logger
     * @param Data                                  $config
     * @param StoreManagerInterface                 $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        CurlFactory $curlFactory,
        Json $json,
        LoggerInterface $logger,
        Data $config,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->curlFactory   = $curlFactory;
        $this->json          = $json;
        $this->logger        = $logger;
        $this->config        = $config;
        $this->storeManager  = $storeManager;
    }

    /**
     * Generic request wrapper with JSON support and retries (low complexity).
     *
     * @param string $method GET|POST|PUT|DELETE
     * @param string $path   Endpoint path starting with "/"
     * @param array<string,mixed>|null $payload
     * @param array<string,string> $extraHeaders
     * @param int|null $storeId
     * @return array{status:int,headers:array<string,string>,body:array<string,mixed>|string,raw:string,url:string}
     */
    public function request(
        string $method,
        string $path,
        ?array $payload = null,
        array $extraHeaders = [],
        ?int $storeId = 0
    ): array {
        if (!$this->config->isEnabled($storeId)) {
            return $this->disabledResponse();
        }

        $url        = $this->normalizeUrl($path, (int)$storeId);
        $curl       = $this->prepareCurl((int)$storeId, $extraHeaders);
        $http       = strtoupper($method);
        $retries    = max(0, $this->config->getRetries($storeId));
        $retryDelay = max(0, $this->config->getRetryDelayMs($storeId));

        // Pre-serialize payload once (only used by POST/PUT)
        $encodedPayload = $this->encodePayload($payload);

        // Dispatch map to avoid switch/if ladders
        $dispatch = $this->httpDispatchMap($curl, $encodedPayload);

        for ($attempt = 1; $attempt <= ($retries + 1); $attempt++) {
            try {
                $finalUrl = $http === 'GET' ? $this->appendQuery($url, $payload) : $url;

                if (!isset($dispatch[$http])) {
                    throw new InvalidArgumentException('Unsupported HTTP method: ' . $method);
                }
                $dispatch[$http]($finalUrl);

                $status  = (int)$curl->getStatus();
                $rawBody = (string)$curl->getBody();
                $body    = $this->decodeJsonSafe($rawBody);

                if ($status < 200 || $status >= 300) {
                    $this->logger->warning('[Ecommerce66_AiCore] Non-2xx response', [
                        'status'  => $status,
                        'url'     => $finalUrl,
                        'body'    => $rawBody,
                        'attempt' => $attempt,
                    ]);
                }

                return [
                    'status'  => $status,
                    'headers' => [], // Curl wrapper does not expose headers directly
                    'body'    => $body,
                    'raw'     => $rawBody,
                    'url'     => $finalUrl,
                ];
            } catch (\Throwable $e) {
                $this->logger->error('[Ecommerce66_AiCore] HTTP request failed', [
                    'exception' => $e->getMessage(),
                    'url'       => $url,
                    'attempt'   => $attempt,
                ]);

                if ($attempt <= $retries) {
                    $this->sleepMs($retryDelay);
                    continue;
                }

                return $this->errorResponse($e, $url);
            }
        }

        // Unreachable, but keeps static analyzers happy
        return $this->errorResponse(null, $url);
    }

    /** @return array{status:int,headers:array<string,string>,body:array<string,mixed>|string,raw:string,url:string} */
    private function disabledResponse(): array
    {
        return [
            'status'  => 503,
            'headers' => [],
            'body'    => ['error' => 'AiCore disabled'],
            'raw'     => '',
            'url'     => '',
        ];
    }

    /** Build absolute URL (base + path). */
    private function normalizeUrl(string $path, int $storeId): string
    {
        $base = rtrim((string)$this->config->getBaseUrl($storeId), '/');
        $p    = '/' . ltrim($path, '/');
        return $base . $p;
    }

    /** Prepare Curl client with timeout & headers. */
    private function prepareCurl(int $storeId, array $extraHeaders): \Magento\Framework\HTTP\Client\Curl
    {
        /** @var \Magento\Framework\HTTP\Client\Curl $curl */
        $curl = $this->curlFactory->create();

        $curl->setTimeout(max(1, (int)$this->config->getTimeout($storeId)));
        $curl->setOption(CURLOPT_RETURNTRANSFER, true);

        $headers = array_merge($this->defaultHeaders($storeId), $extraHeaders);
        foreach ($headers as $k => $v) {
            $curl->addHeader((string)$k, (string)$v);
        }
        return $curl;
    }

    /** Default JSON/API headers. */
    private function defaultHeaders(int $storeId): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
            'X-API-Key'    => (string)$this->config->getApiKey($storeId),
            'X-Client-Id'  => (string)$this->config->getClientId($storeId),
        ];
    }

    /** Pre-encode payload for non-GET methods (keeps loop simple). */
    private function encodePayload(?array $payload): string
    {
        if ($payload === null || $payload === []) {
            return '{}';
        }
        return $this->json->serialize($payload);
    }

    /** Append query string for GET requests (no branching in main loop). */
    private function appendQuery(string $url, ?array $payload): string
    {
        if (empty($payload)) {
            return $url;
        }
        $qs = http_build_query($payload);
        return $url . (str_contains($url, '?') ? '&' : '?') . $qs;
    }

    /**
     * Return a method map that executes the HTTP verb without switch/case.
     *
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param string $encodedPayload
     * @return array<string, callable(string):void>
     */
    private function httpDispatchMap(\Magento\Framework\HTTP\Client\Curl $curl, string $encodedPayload): array
    {
        return [
            'GET'    => static function (string $url) use ($curl): void { 
                $curl->get($url); 
            },
            'POST'   => static function (string $url) use ($curl, $encodedPayload): void { 
                $curl->post($url, $encodedPayload); 
            },
            'PUT'    => static function (string $url) use ($curl, $encodedPayload): void { 
                // Magento Curl doesn't have put() method, so we use setOption
                $curl->setOption(CURLOPT_CUSTOMREQUEST, 'PUT');
                $curl->setOption(CURLOPT_POSTFIELDS, $encodedPayload);
                $curl->get($url); // Actually executes the request
            },
            'DELETE' => static function (string $url) use ($curl): void { 
                // Magento Curl doesn't have delete() method either
                $curl->setOption(CURLOPT_CUSTOMREQUEST, 'DELETE');
                $curl->get($url); // Actually executes the request
            },
        ];
    }

    /** Decode JSON; fall back to raw string on parse failure. */
    private function decodeJsonSafe(string $raw)
    {
        try {
            $decoded = $this->decodeJson($raw); // usa tu helper existente
            return $decoded ?? $raw;
        } catch (\Throwable $e) {
            return $raw;
        }
    }

    /** Sleep helper in milliseconds. */
    private function sleepMs(int $ms): void
    {
        if ($ms > 0) {
            usleep($ms * 1000);
        }
    }

    /** @return array{status:int,headers:array<string,string>,body:array<string,mixed>|string,raw:string,url:string} */
    private function errorResponse(?\Throwable $e, string $url): array
    {
        return [
            'status'  => 0,
            'headers' => [],
            'body'    => ['error' => $e ? $e->getMessage() : 'Unknown error'],
            'raw'     => '',
            'url'     => $url,
        ];
    }

    /**
     * @param array    $query
     * @param array    $headers
     * @param int|null $storeId
     *
     * @return array
     */
    public function callHealth(array $query = [], array $headers = [], ?int $storeId = 0): array
    {
        /*if (empty($headers)) {
            $headers = [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
                'X-API-Key'    => (string)$this->config->getApiKey($storeId),
                'X-Client-Id'  => (string)$this->config->getClientId($storeId),
            ];
        }*/

        return $this->request('POST', $this->config->getEndpointHealth($storeId), $query, $headers, $storeId);
    }

    /**
     * @param string $raw
     *
     * @return array|bool|float|int|string|null
     */
    private function decodeJson(string $raw)
    {
        try {
            $decoded = $this->json->unserialize($raw);
            if (is_array($decoded)) {
                return $decoded;
            }
        } catch (\Throwable $e) {
            $e->getMessage();
            // ignore; return raw string above
        }
        return null;
    }
}
