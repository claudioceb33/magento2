<?php
/**
 * Copyright © Ecommerce66. All rights reserved.
 */
declare(strict_types=1);

namespace Ecommerce66\AiSearch\Controller\Products;

use Ecommerce66\AiSearch\Block\Product\RecommendationList;
use Ecommerce66\AiCore\Helper\Data as AiCoreHelper;
use Magento\Catalog\Block\Product\ProductList\Item\AddTo\Compare as CompareBlock;
use Magento\Catalog\Block\Product\ProductList\Item\Container as ProductListContainer;
use Magento\Catalog\Block\Product\ProductList\Toolbar as ToolbarBlock;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\ViewModel\Product\OptionsData;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Element\FormKey;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use stdClass;
use Throwable;
use Magento\Wishlist\Block\Catalog\Product\ProductList\Item\AddTo\Wishlist as WishlistBlock;
use Zend_Db_Expr;

/**
 * Product List Controller
 * 
 * Fetches product data by SKUs and returns rendered HTML using Magento's native product list template
 */
class ListAction implements HttpPostActionInterface
{
    /**
     * @var HttpRequest
     */
    private HttpRequest $request;

    /**
     * @var JsonFactory
     */
    private JsonFactory $jsonFactory;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var LayoutInterface
     */
    private LayoutInterface $layout;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $productCollectionFactory;

    /**
     * @var OptionsData
     */
    private OptionsData $productOptionsData;

    /**
     * @var Curl
     */
    private Curl $httpClient;

    /**
     * @var SessionManagerInterface
     */
    private SessionManagerInterface $sessionManager;

    /**
     * @var AiCoreHelper
     */
    private AiCoreHelper $aiCoreHelper;

    /**
     * Constructor
     *
     * @param HttpRequest $request
     * @param JsonFactory $jsonFactory
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param LayoutInterface $layout
     * @param CollectionFactory $productCollectionFactory
     * @param OptionsData $productOptionsData
     * @param Curl $httpClient
     * @param SessionManagerInterface $sessionManager
     * @param AiCoreHelper $aiCoreHelper
     */
    public function __construct(
        HttpRequest $request,
        JsonFactory $jsonFactory,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        LayoutInterface $layout,
        CollectionFactory $productCollectionFactory,
        OptionsData $productOptionsData,
        Curl $httpClient,
        SessionManagerInterface $sessionManager,
        AiCoreHelper $aiCoreHelper
    ) {
        $this->request = $request;
        $this->jsonFactory = $jsonFactory;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->layout = $layout;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productOptionsData = $productOptionsData;
        $this->httpClient = $httpClient;
        $this->sessionManager = $sessionManager;
        $this->aiCoreHelper = $aiCoreHelper;
    }

    /**
     * Execute action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $result = $this->jsonFactory->create();

        try {
            $requestData = $this->extractRequestData($result);
            if ($requestData === null) {
                return $result;
            }

            $collection = $this->createProductCollection($requestData['skus']);
            if (!$collection->getSize()) {
                return $this->buildEmptyResponse($result);
            }

            $html = $this->renderProductList($collection);
            $this->notifySearchAnalytics($requestData['prompt'], $collection);

            $recommendations = $this->buildRecommendations($requestData['skus']);

            return $this->buildSuccessResponse(
                $result,
                $html,
                (int)$collection->getSize(),
                $recommendations
            );
        } catch (Throwable $exception) {
            return $this->handleException($result, $exception);
        }
    }

    /**
     * Decode request payload and validate SKU input.
     */
    private function extractRequestData(ResultInterface $result): ?array
    {
        $content = $this->request->getContent();
        $data = json_decode($content ?? '', true);

        if (!is_array($data)) {
            $result->setData([
                'success' => false,
                'message' => __('Invalid request payload.')
            ]);
            return null;
        }

        if (!isset($data['skus']) || !is_array($data['skus'])) {
            $result->setData([
                'success' => false,
                'message' => __('No SKUs provided.')
            ]);
            return null;
        }

        $skus = array_values(array_filter(array_map('trim', $data['skus'])));
        if ($skus === []) {
            $result->setData([
                'success' => false,
                'message' => __('Invalid SKUs provided.')
            ]);
            return null;
        }

        $prompt = isset($data['prompt']) ? trim((string)$data['prompt']) : '';

        return [
            'skus' => $skus,
            'prompt' => $prompt
        ];
    }

    /**
     * Prepare product collection with all required attributes and ordering.
     */
    private function createProductCollection(array $skus): Collection
    {
        /** @var Collection $collection */
        $collection = $this->productCollectionFactory->create();
        $collection->setStoreId((int)$this->storeManager->getStore()->getId());
        $collection->addAttributeToSelect([
            'name',
            'sku',
            'short_description',
            'small_image'
        ]);
        $collection->addAttributeToFilter('sku', ['in' => $skus]);
        $collection->addAttributeToFilter('status', Status::STATUS_ENABLED);
        $collection->addAttributeToFilter(
            'visibility',
            ['in' => [
                Visibility::VISIBILITY_IN_CATALOG,
                Visibility::VISIBILITY_IN_SEARCH,
                Visibility::VISIBILITY_BOTH
            ]]
        );
        $collection->addMinimalPrice();
        $collection->addFinalPrice();
        $collection->addTaxPercents();
        $collection->addUrlRewrite();
        $collection->setPageSize(count($skus));

        $this->applyRequestedOrder($collection, $skus);

        return $collection;
    }

    /**
     * Apply the requested SKU order when building the collection select.
     */
    private function applyRequestedOrder(Collection $collection, array $skus): void
    {
        $connection = $collection->getConnection();
        $quotedSkus = array_map([$connection, 'quote'], $skus);

        if ($quotedSkus === []) {
            return;
        }

        $collection->getSelect()->order(
            new Zend_Db_Expr(sprintf('FIELD(e.sku, %s)', implode(',', $quotedSkus)))
        );
    }

    /**
     * Render the product list block with toolbar/add-to containers.
     */
    private function renderProductList(Collection $collection, string $blockName = 'ai.search.product.list'): string
    {
        /** @var RecommendationList $productListBlock */
        $productListBlock = $this->layout->createBlock(
            RecommendationList::class,
            $blockName
        );

        $productListBlock->setProductCollection($collection);
        $productListBlock->setTemplate('Magento_Catalog::product/list.phtml');
        $productListBlock->setData('mode', 'grid');
        $productListBlock->setData('viewModel', $this->productOptionsData);
        $productListBlock->setChild('addto', $this->createAddToContainer());
        $productListBlock->setChild('toolbar', $this->createToolbarBlock());

        $this->layout->createBlock(FormKey::class, 'formkey');

        return $productListBlock->toHtml();
    }

    /**
     * Create the add-to container with compare and wishlist buttons.
     */
    private function createAddToContainer(): ProductListContainer
    {
        /** @var ProductListContainer $container */
        $container = $this->layout->createBlock(
            ProductListContainer::class,
            'ai.search.product.addto'
        );

        /** @var CompareBlock $compareBlock */
        $compareBlock = $this->layout->createBlock(
            CompareBlock::class,
            'ai.search.product.compare'
        );
        $compareBlock->setTemplate('Magento_Catalog::product/list/addto/compare.phtml');
        $container->setChild('compare', $compareBlock);

        $this->attachWishlistBlock($container);

        return $container;
    }

    /**
     * Attach wishlist block when module is available.
     */
    private function attachWishlistBlock(ProductListContainer $container): void
    {
        try {
            /** @var WishlistBlock $wishlistBlock */
            $wishlistBlock = $this->layout->createBlock(
                WishlistBlock::class,
                'ai.search.product.wishlist'
            );
            $wishlistBlock->setTemplate('Magento_Wishlist::catalog/product/list/addto/wishlist.phtml');
            $container->setChild('wishlist', $wishlistBlock);
        } catch (Throwable $exception) {
            $this->logger->info('Wishlist block not available for AI Search');
        }
    }

    /**
     * Create a stub toolbar without pagination controls.
     */
    private function createToolbarBlock(): ToolbarBlock
    {
        /** @var ToolbarBlock $toolbarBlock */
        $toolbarBlock = $this->layout->createBlock(
            ToolbarBlock::class,
            'ai.search.product.toolbar'
        );
        $toolbarBlock->setTemplate('Ecommerce66_AiSearch::widget/toolbar-empty.phtml');

        return $toolbarBlock;
    }

    /**
     * Build response when no products match the given SKUs.
     */
    private function buildEmptyResponse(ResultInterface $result): ResultInterface
    {
        return $result->setData([
            'success' => true,
            'html' => '<div class="message info"><div>' . __('No products found for the provided SKUs.') . '</div></div>',
            'count' => 0
        ]);
    }

    /**
     * Build success payload for the frontend consumer.
     */
    private function buildSuccessResponse(
        ResultInterface $result,
        string $html,
        int $count,
        array $recommendations = []
    ): ResultInterface {
        $response = [
            'success' => true,
            'html' => $html,
            'count' => $count
        ];

        if (!empty($recommendations)) {
            $response['recommendations'] = $recommendations;
        }

        return $result->setData($response);
    }

    /**
     * Log the exception and return a generic error payload.
     */
    private function handleException(ResultInterface $result, Throwable $exception): ResultInterface
    {
        $this->logger->critical('Product List Controller Exception', [
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        return $result->setData([
            'success' => false,
            'message' => __('An error occurred while fetching product data.')
        ]);
    }

    private function notifySearchAnalytics(string $prompt, Collection $collection): void
    {
        try {
            $collection->load();

            $resultsCount = (int)$collection->getSize();
            $summary = [];

            foreach ($collection as $product) {
                $summary[] = (string)$product->getName();
                if (count($summary) >= 10) {
                    break;
                }
            }

            $payload = [
                'query_text' => $prompt,
                'query_config' => new stdClass(),
                'results_count' => $resultsCount,
                'results_summary' => $summary,
                'user_id' => $this->sessionManager->getSessionId() ?: 'anonymous'
            ];

            $baseUrl = rtrim($this->aiCoreHelper->getBaseUrl(), '/');
            $client_code = $this->aiCoreHelper->getCode();
            $endpoint = $baseUrl . '/rest/v1/clients/' . $client_code . '/search-queries';

            $this->httpClient->addHeader('Content-Type', 'application/json');
            $this->httpClient->post($endpoint, json_encode($payload));
        } catch (\Exception $exception) {
            $this->logger->warning('Client search telemetry failed', [
                'message' => $exception->getMessage()
            ]);
        }
    }

    /**
     * Build recommendation payload when possible.
     *
     * @param string[] $skus
     * @return array
     */
    private function buildRecommendations(array $skus): array
    {
      
        $primarySku = $skus[0] ?? '';
        if ($primarySku === '') {
            return [];
        }

        try {
            $recommendationSkus = $this->requestRecommendationSkus($primarySku);
             
            if ($recommendationSkus === []) {
                return [];
            }

            $recommendationSkus = array_values(array_diff(
                array_unique(array_map('trim', $recommendationSkus)),
                $skus
            ));

            if ($recommendationSkus === []) {
                return [];
            }

            $recommendationSkus = array_slice($recommendationSkus, 0, 8);
            $collection = $this->createProductCollection($recommendationSkus);
            if (!$collection->getSize()) {
                return [];
            }

            return [
                'html' => $this->renderProductList($collection, 'ai.search.product.recommendations'),
                'count' => (int)$collection->getSize(),
                'title' => __('También puede interesarte')
            ];
        } catch (Throwable $exception) {
            $this->logger->warning('Unable to build AI recommendations', [
                'message' => $exception->getMessage()
            ]);
        }

        return [];
    }

    /**
     * Call AI recommendation service and extract SKUs.
     *
     * @param string $sku
     * @return string[]
     */
    private function requestRecommendationSkus(string $sku): array
    {
        $baseUrl = $this->aiCoreHelper->getBaseUrl();
        if ($baseUrl === '') {
            $baseUrl = 'https://ai.66ecommerce.com';
        }

        $endpoint = rtrim($baseUrl, '/') . '/api/v1/recommendations/' . rawurlencode($sku) . '/related';

        try {
            $storeId = (int)$this->storeManager->getStore()->getId();

            $this->httpClient->setHeaders([]);
            $this->httpClient->addHeader('Accept', 'application/json');
            $this->httpClient->addHeader('Content-Type', 'application/json');

            $apiKey = $this->aiCoreHelper->getApiKey($storeId);
            if ($apiKey !== '') {
                $this->httpClient->addHeader('X-API-Key', $apiKey);
            }

            $clientId = $this->aiCoreHelper->getClientId($storeId);
            if ($clientId !== '') {
                $this->httpClient->addHeader('X-Client-Id', $clientId);
            }

            $this->httpClient->get($endpoint);

            if ((int)$this->httpClient->getStatus() !== 200) {
                $this->logger->warning('Recommendation endpoint returned non-200 status', [
                    'status' => $this->httpClient->getStatus(),
                    'endpoint' => $endpoint
                ]);
                return [];
            }

            $body = $this->httpClient->getBody();
            $decoded = json_decode($body, true);
            if (!is_array($decoded)) {
                $this->logger->warning('Recommendation endpoint returned invalid JSON', [
                    'endpoint' => $endpoint
                ]);
                return [];
            }

            return $this->extractRecommendationSkus($decoded);
        } catch (Throwable $exception) {
            $this->logger->warning('Recommendation endpoint request failed', [
                'endpoint' => $endpoint,
                'message' => $exception->getMessage()
            ]);
        }

        return [];
    }

    /**
     * Accept multiple response shapes from the recommendation service.
     *
     * @param array $payload
     * @return string[]
     */
    private function extractRecommendationSkus(array $payload): array
    {
        $skus = $this->extractFromSkusKey($payload);
        if ($skus !== []) {
            return $skus;
        }

        $skus = $this->extractFromDataKey($payload);
        if ($skus !== []) {
            return $skus;
        }

        $skus = $this->extractFromResultsKey($payload);
        if ($skus !== []) {
            return $skus;
        }

        return [];
    }

    private function extractFromSkusKey(array $payload): array
    {
        if (!isset($payload['skus']) || !is_array($payload['skus'])) {
            return [];
        }

        return $this->collectSkuValues($payload['skus']);
    }

    private function extractFromDataKey(array $payload): array
    {
        if (!isset($payload['data']) || !is_array($payload['data'])) {
            return [];
        }

        return $this->collectSkuValues($payload['data']);
    }

    private function extractFromResultsKey(array $payload): array
    {
        if (!isset($payload['results']) || !is_array($payload['results'])) {
            return [];
        }

        return $this->collectSkuValues($payload['results']);
    }

    private function collectSkuValues(array $items): array
    {
        $skus = [];

        foreach ($items as $item) {
            $sku = $this->resolveSkuValue($item);
            if ($sku !== null) {
                $skus[] = $sku;
            }
        }

        return array_values($skus);
    }

    private function resolveSkuValue($item): ?string
    {
        if (is_string($item)) {
            $item = trim($item);
            return $item === '' ? null : $item;
        }

        if (!is_array($item) || !isset($item['sku'])) {
            return null;
        }

        $value = trim((string)$item['sku']);

        return $value === '' ? null : $value;
    }
}
