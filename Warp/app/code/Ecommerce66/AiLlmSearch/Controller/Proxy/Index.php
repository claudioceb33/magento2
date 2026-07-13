<?php
declare(strict_types=1);

namespace Ecommerce66\AiLlmSearch\Controller\Proxy;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Ecommerce66\AiLlmSearch\Helper\Config as WidgetConfig;
use Ecommerce66\AiCore\Helper\Connect;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Data\Form\FormKey;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use InvalidArgumentException;

/**
 * Proxy controller that forwards search requests to the configured AiCore endpoint
 * server-side, adding the API key from configuration. This prevents exposing
 * the external API key and endpoint to the browser.
 */
class Index extends Action implements CsrfAwareActionInterface
{
    private WidgetConfig $config;
    private Connect $connect;
    private JsonFactory $jsonFactory;
    private LoggerInterface $logger;
    private FormKey $formKey;
    private StoreManagerInterface $storeManager;
    private ProductRepositoryInterface $productRepository;
    private LayoutFactory $layoutFactory;

    public function __construct(
        Context $context,
        WidgetConfig $config,
        Connect $connect,
        JsonFactory $jsonFactory,
        LoggerInterface $logger,
        FormKey $formKey,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        LayoutFactory $layoutFactory
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->connect = $connect;
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->formKey = $formKey;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * Bypass default CSRF validator and implement custom validation that accepts
     * the Magento form key either as POST param or inside a JSON body.
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        if (!($request instanceof HttpRequest)) {
            return false;
        }

        $formKeyValue = $request->getParam('form_key');
        if (!$formKeyValue) {
            $body = $request->getContent();
            if ($body) {
                $decoded = json_decode($body, true);
                if (is_array($decoded) && !empty($decoded['form_key'])) {
                    $formKeyValue = $decoded['form_key'];
                }
            }
        }

        if (!$formKeyValue) {
            $formKeyValue = $request->getHeader('X-Form-Key') ?: null;
        }

        return $formKeyValue === $this->formKey->getFormKey();
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        $request = $this->getRequest();
        if ($this->isInvalidRequest($request)) {
            return $this->prepareErrorResult($result, 'Invalid request method');
        }

        try {
            $rawData = $this->decodeRequestData($request);
            $storeId = $this->resolveStoreId($request->getParam('store'), $request->getParam('store_id'));
            $upstream = $this->buildUpstreamPayload($rawData, $storeId);
            $response = $this->sendUpstreamRequest($upstream, $storeId);

            return $this->handleUpstreamResponse($response, $storeId, $result);
        } catch (InvalidArgumentException $e) {
            return $this->prepareErrorResult($result, $e->getMessage());
        } catch (\Throwable $e) {
            $this->logger->critical($e->getMessage(), ['exception' => $e]);
            return $this->prepareErrorResult($result, 'Proxy error', 500);
        }
    }

    /**
     * @param mixed $storeParam
     * @param mixed $storeIdParam
     */
    private function resolveStoreId($storeParam, $storeIdParam): int
    {
        if (is_numeric($storeParam)) {
            return (int)$storeParam;
        }
        if (is_numeric($storeIdParam)) {
            return (int)$storeIdParam;
        }
        try {
            return (int)$this->storeManager->getStore()->getId();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function isInvalidRequest(?RequestInterface $request): bool
    {
        return !($request instanceof HttpRequest) || !$request->isPost();
    }

    private function decodeRequestData(HttpRequest $request): array
    {
        $content = $request->getContent();
        if ($content) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        $params = $request->getParams();
        return is_array($params) ? $params : [];
    }

    private function buildUpstreamPayload(array $data, int $storeId): array
    {
        $query = trim((string)($data['query'] ?? $data['q'] ?? ''));
        if ($query === '') {
            throw new InvalidArgumentException('Missing query');
        }

        return [
            'query' => $query,
            'session_id' => $this->normalizeSessionId($data['session_id'] ?? null),
            'context' => $this->normalizeContext($data['context'] ?? null),
            'seller_id' => $this->resolveSellerIdFromPayload($data, $storeId),
            'client_id' => $this->config->getCode($storeId) ?: null,
            'action' => $this->normalizeAction($data['action'] ?? ''),
            'selected_products' => $this->normalizeSelectedProducts($data['selected_products'] ?? null),
        ];
    }

    private function sendUpstreamRequest(array $payload, int $storeId): array
    {
        $path = $this->config->getCopilotEndpoint($storeId);
        $extraHeaders = [
            'X-Form-Key' => $this->formKey->getFormKey(),
            '__timeout' => $this->config->getCopilotTimeout($storeId),
        ];

        return $this->connect->request('POST', $path, $payload, $extraHeaders, $storeId);
    }

    /**
     * @param array<string, mixed>|string $responseBody
     */
    private function handleUpstreamResponse(array $response, int $storeId, \Magento\Framework\Controller\Result\Json $result)
    {
        $status = (int)($response['status'] ?? 0);
        $rawBody = (string)($response['raw'] ?? '');
        $body = $response['body'] ?? $rawBody;

        if ($status >= 200 && $status < 300) {
            $payload = $this->prepareSuccessPayload($body, $rawBody, $storeId);
            return $result->setData($payload);
        }

        $this->logger->error('Ecommerce66 LLM proxy upstream error', [
            'status' => $status,
            'url' => $response['url'] ?? '',
            'body' => $rawBody
        ]);

        return $result->setHttpResponseCode(502)->setData([
            'error' => true,
            'message' => 'Upstream error',
            'status' => $status,
            'raw' => $rawBody
        ]);
    }

    /**
     * @param array<string, mixed>|string $body
     */
    private function prepareSuccessPayload($body, string $rawBody, int $storeId): array
    {
        if (is_array($body)) {
            $body['product_html'] = $this->renderProductHtml($body['products'] ?? [], $storeId);
            return $body;
        }

        if (is_string($body) && $body !== '') {
            $decoded = json_decode($body, true);
            if (is_array($decoded)) {
                $decoded['product_html'] = $this->renderProductHtml($decoded['products'] ?? [], $storeId);
                return $decoded;
            }
        }

        return ['raw' => $rawBody];
    }

    private function prepareErrorResult(
        \Magento\Framework\Controller\Result\Json $result,
        string $message,
        int $httpCode = 200
    ) {
        if ($httpCode !== 200) {
            $result->setHttpResponseCode($httpCode);
        }

        return $result->setData(['error' => true, 'message' => $message]);
    }

    /**
     * @param mixed $sessionId
     */
    private function normalizeSessionId($sessionId): ?string
    {
        $value = trim((string)$sessionId);
        return $value !== '' ? $value : null;
    }

    /**
     * @param mixed $context
     */
    private function normalizeContext($context): ?array
    {
        return is_array($context) ? $context : null;
    }

    private function resolveSellerIdFromPayload(array $data, int $storeId): ?int
    {
        if (isset($data['seller_id']) && is_numeric($data['seller_id'])) {
            return (int)$data['seller_id'];
        }

        $defaultSellerId = $this->config->getDefaultSellerId($storeId);
        return $defaultSellerId !== null ? (int)$defaultSellerId : null;
    }

    /**
     * @param mixed $selectedProducts
     */
    private function normalizeSelectedProducts($selectedProducts): ?array
    {
        if (!is_array($selectedProducts)) {
            return null;
        }

        $sanitized = [];
        foreach ($selectedProducts as $item) {
            if (!is_scalar($item)) {
                continue;
            }
            $value = trim((string)$item);
            if ($value !== '') {
                $sanitized[] = $value;
            }
        }

        return $sanitized !== [] ? $sanitized : null;
    }

    private function normalizeAction($action): string
    {
        $value = (string)$action;
        return in_array($value, ['search', 'compare', 'recommend'], true) ? $value : 'search';
    }

    /**
     * @param array<int, array<string, mixed>> $products
     */
    private function renderProductHtml(array $products, int $storeId): string
    {
        $skus = [];
        foreach ($products as $item) {
            if (is_array($item) && isset($item['sku']) && $item['sku'] !== '') {
                $skus[] = (string)$item['sku'];
            }
        }

        if ($skus === []) {
            return '';
        }

        $resolvedProducts = [];
        foreach ($skus as $sku) {
            try {
                $product = $this->productRepository->get($sku, false, $storeId);
                $product->setStoreId($storeId);
                $resolvedProducts[] = $product;
            } catch (NoSuchEntityException $e) {
                $this->logger->warning('AI Copilot returned unknown SKU', ['sku' => $sku]);
            }
        }

        if ($resolvedProducts === []) {
            return '';
        }

        $layout = $this->layoutFactory->create();
        /** @var \Ecommerce66\AiLlmSearch\Block\ProductList $block */
        $block = $layout->createBlock(\Ecommerce66\AiLlmSearch\Block\ProductList::class);
        $block->setProducts($resolvedProducts);
        $block->setTemplate('Ecommerce66_AiLlmSearch::product/list.phtml');

        return $block->toHtml();
    }
}
