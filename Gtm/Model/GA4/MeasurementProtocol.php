<?php

namespace Ceb\Gtm\Model\GA4;

use Ceb\Gtm\Helper\Data as GtmHelper;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

class MeasurementProtocol
{
    public const GA_ENDPOINT = 'https://www.google-analytics.com/mp/collect';
    public const DEBUG_ENDPOINT = 'https://www.google-analytics.com/debug/mp/collect';

    /**
     * @var CurlFactory
     */
    protected $curlFactory;

    /**
     * @var GtmHelper
     */
    protected $gtmHelper;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        CurlFactory $curlFactory,
        GtmHelper $gtmHelper,
        Json $json,
        LoggerInterface $logger
    ) {
        $this->curlFactory = $curlFactory;
        $this->gtmHelper = $gtmHelper;
        $this->json = $json;
        $this->logger = $logger;
    }

    /**
     * Send purchase event to GA4 Measurement Protocol.
     *
     * Important:
     * - Real events are always sent to /mp/collect.
     * - In debug mode, a copy is also sent to /debug/mp/collect for validation only.
     *
     * @param OrderInterface $order
     * @param string $clientId
     * @param string|null $sessionId
     * @return bool
     */
    public function sendPurchase(OrderInterface $order, string $clientId, ?string $sessionId = null): bool
    {
        $requestConfig = $this->getRequestConfig();
        $clientId = trim((string)$clientId);

        if ($requestConfig === null) {
            return false;
        }

        if ($clientId === '') {
            $this->logger->warning(sprintf(
                'GA4 Measurement Protocol: Skipping Order #%s because client_id is empty',
                $order->getIncrementId()
            ));
            return false;
        }

        $payload = $this->buildPayload($order, $clientId, $sessionId);

        if ($payload === []) {
            $this->logger->warning(sprintf(
                'GA4 Measurement Protocol: Skipping Order #%s because payload is empty',
                $order->getIncrementId()
            ));
            return false;
        }

        try {
            $this->validateDebugPurchase($order, $payload, $requestConfig);
            return $this->sendRequest($order, $payload, $this->buildRequestUrl($requestConfig));
        } catch (\Exception $e) {
            $this->logger->error(sprintf(
                'GA4 Measurement Protocol Exception for Order #%s: %s',
                $order->getIncrementId(),
                $e->getMessage()
            ));

            return false;
        }
    }

    protected function getRequestConfig(): ?array
    {
        $measurementId = $this->gtmHelper->getMeasurementId();
        $apiSecret = $this->gtmHelper->getApiSecret();
        if (!$measurementId || !$apiSecret) {
            $this->logger->error('GA4 Measurement Protocol: Missing config (Measurement ID or API Secret)');
            return null;
        }

        return [
            'measurement_id' => trim((string)$measurementId),
            'api_secret' => trim((string)$apiSecret),
        ];
    }

    protected function buildRequestUrl(array $requestConfig, string $endpoint = self::GA_ENDPOINT): string
    {
        return $endpoint . '?' . http_build_query($requestConfig);
    }

    protected function sendRequest(OrderInterface $order, array $payload, string $url): bool
    {
        $payloadJson = $this->json->serialize($payload);
        $this->logDebugRequest($order, $payloadJson);

        /** @var \Magento\Framework\HTTP\Client\Curl $curl */
        $curl = $this->curlFactory->create();
        $curl->setOption(CURLOPT_TIMEOUT, 10);
        $curl->addHeader('Content-Type', 'application/json');
        $curl->post($url, $payloadJson);

        $responseCode = $curl->getStatus();
        $responseBody = $curl->getBody();
        $this->logDebugResponse($responseCode, $responseBody);

        if ($this->isSuccessfulResponse((int)$responseCode)) {
            return true;
        }

        $this->logger->error(sprintf(
            'GA4 Measurement Protocol Error for Order #%s: %s (Code: %s)',
            $order->getIncrementId(),
            $responseBody,
            $responseCode
        ));
        return false;
    }

    protected function logDebugRequest(OrderInterface $order, string $payloadJson): void
    {
        if (!$this->canLogDebugInfo()) {
            return;
        }

        $this->logger->info('GA4 Payload for Order #' . $order->getIncrementId() . ': ' . $payloadJson);
    }

    protected function logDebugResponse(int $responseCode, string $responseBody): void
    {
        if (!$this->canLogDebugInfo()) {
            return;
        }

        $this->logger->info('GA4 Response Code: ' . $responseCode);
        $this->logger->info('GA4 Response Body: ' . $responseBody);
    }

    protected function canLogDebugInfo(): bool
    {
        return $this->gtmHelper->isDebugMode();
    }

    protected function isSuccessfulResponse(int $responseCode): bool
    {
        return $responseCode >= 200 && $responseCode < 300;
    }

    /**
     * Build GA4 Measurement Protocol payload.
     *
     * @param OrderInterface $order
     * @param string $clientId
     * @param string|null $sessionId
     * @return array
     */
    protected function buildPayload(OrderInterface $order, string $clientId, ?string $sessionId = null): array
    {
        $transactionId = trim((string)$order->getIncrementId());

        if ($transactionId === '') {
            return [];
        }

        $normalizedSessionId = $this->normalizeSessionId($sessionId);
        $items = $this->buildItems($order);

        if (empty($items)) {
            return [];
        }

        $payload = [
            'client_id' => trim((string)$clientId),
            'timestamp_micros' => (int)(microtime(true) * 1000000),
            'events' => [
                [
                    'name' => 'purchase',
                    'params' => [
                        'transaction_id' => $transactionId,
                        'order_increment_id' => $transactionId,
                        'event_source' => 'server',
                        'mp_source' => 'magento_cron',
                        'value' => (float)$order->getGrandTotal(),
                        'currency' => (string)$order->getOrderCurrencyCode(),
                        'tax' => (float)$order->getTaxAmount(),
                        'shipping' => (float)$order->getShippingAmount(),
                        'coupon' => (string)($order->getCouponCode() ?: ''),
                        'items' => $items,
                        'engagement_time_msec' => 100
                    ]
                ]
            ]
        ];

        if ($this->gtmHelper->isDebugMode()) {
            $payload['events'][0]['params']['debug_mode'] = 1;
        }

        if ($normalizedSessionId !== null) {
            $payload['events'][0]['params']['ga_session_id'] = $normalizedSessionId;
        }

        return $payload;
    }

    /**
     * Build GA4 ecommerce items from order visible items.
     *
     * @param OrderInterface $order
     * @return array
     */
    protected function buildItems(OrderInterface $order): array
    {
        $items = [];

        foreach ($order->getAllVisibleItems() as $item) {
            $itemData = [
                'item_id' => (string)$item->getSku(),
                'item_name' => (string)$item->getName(),
                'price' => (float)$item->getPrice(),
                'quantity' => (int)$item->getQtyOrdered()
            ];

            $product = $item->getProduct();

            if ($product) {
                $brand = $this->gtmHelper->getProductBrand($product);
                if ($brand) {
                    $itemData['item_brand'] = (string)$brand;
                }

                $category = $this->gtmHelper->getProductCategory($product);
                if ($category) {
                    $itemData['item_category'] = (string)$category;
                }
            }

            $items[] = $itemData;
        }

        return $items;
    }

    /**
     * Validate payload against GA4 Measurement Protocol validation endpoint.
     *
     * This does not ingest events into GA4 reports.
     *
     * @param array $payload
     * @param string $measurementId
     * @param string $apiSecret
     * @return string
     */
    protected function validatePayload(array $payload, string $measurementId, string $apiSecret): string
    {
        $url = $this->buildRequestUrl([
            'measurement_id' => $measurementId,
            'api_secret' => $apiSecret
        ], self::DEBUG_ENDPOINT);

        try {
            $curl = $this->curlFactory->create();
            $curl->setOption(CURLOPT_TIMEOUT, 10);
            $curl->addHeader('Content-Type', 'application/json');
            $curl->post($url, $this->json->serialize($payload));

            return sprintf(
                'Code: %s Body: %s',
                (int)$curl->getStatus(),
                (string)$curl->getBody()
            );
        } catch (\Exception $e) {
            return 'Validation exception: ' . $e->getMessage();
        }
    }

    protected function validateDebugPurchase(OrderInterface $order, array $payload, array $requestConfig): void
    {
        if (!$this->gtmHelper->isDebugMode()) {
            return;
        }

        $validationPayload = $payload;
        $validationPayload['validation_behavior'] = 'ENFORCE_RECOMMENDATIONS';

        $validationResponse = $this->validatePayload(
            $validationPayload,
            (string)$requestConfig['measurement_id'],
            (string)$requestConfig['api_secret']
        );

        $this->logger->info(sprintf(
            'GA4 Validation Response for Order #%s: %s',
            $order->getIncrementId(),
            $validationResponse
        ));
    }

    /**
     * Normalize GA session id to GA4 expected numeric format.
     *
     * @param string|null $sessionId
     * @return int|null
     */
    protected function normalizeSessionId(?string $sessionId): ?int
    {
        if ($sessionId === null) {
            return null;
        }

        $sessionId = trim((string)$sessionId);

        if ($sessionId === '') {
            return null;
        }

        if (!ctype_digit($sessionId)) {
            return null;
        }

        $normalized = (int)$sessionId;

        if ($normalized <= 0) {
            return null;
        }

        return $normalized;
    }
}
