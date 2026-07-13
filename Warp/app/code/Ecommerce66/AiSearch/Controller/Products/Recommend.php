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
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Element\FormKey;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use Magento\Wishlist\Block\Catalog\Product\ProductList\Item\AddTo\Wishlist as WishlistBlock;
use Zend_Db_Expr;

/**
 * Recommendations Controller
 *
 * Generates additional product recommendations after the main AI search results.
 */
class Recommend implements HttpPostActionInterface
{
    private const DISPLAY_MODE_SIDEBAR = 'sidebar';
    private const DISPLAY_MODE_WITH_ACTIONS = 'with_actions';

    private HttpRequest $request;
    private JsonFactory $jsonFactory;
    private StoreManagerInterface $storeManager;
    private LoggerInterface $logger;
    private LayoutInterface $layout;
    private CollectionFactory $productCollectionFactory;
    private OptionsData $productOptionsData;
    private Curl $httpClient;
    private AiCoreHelper $aiCoreHelper;

    public function __construct(
        HttpRequest $request,
        JsonFactory $jsonFactory,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        LayoutInterface $layout,
        CollectionFactory $productCollectionFactory,
        OptionsData $productOptionsData,
        Curl $httpClient,
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
        $this->aiCoreHelper = $aiCoreHelper;
    }

    public function execute(): ResultInterface
    {
        $result = $this->jsonFactory->create();

        try {
            $requestData = $this->extractRequestData($result);
            if ($requestData === null) {
                return $result;
            }

            $recommendations = $this->buildRecommendations(
                $requestData['primary_sku'],
                $requestData['skus']
            );

            if ($recommendations === []) {
                return $result->setData([
                    'success' => true,
                    'html' => '',
                    'count' => 0
                ]);
            }

            return $result->setData([
                'success' => true,
                'html' => $recommendations['html'],
                'count' => $recommendations['count'],
                'title' => $recommendations['title']
            ]);
        } catch (Throwable $exception) {
            $this->logger->critical('Recommendations Controller Exception', [
                'exception' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);

            return $result->setData([
                'success' => false,
                'message' => __('An error occurred while fetching recommendations.')
            ]);
        }
    }

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

        $primarySku = isset($data['primary_sku']) ? trim((string)$data['primary_sku']) : '';
        $skus = [];
        if (isset($data['skus']) && is_array($data['skus'])) {
            $skus = array_values(array_filter(array_map('trim', $data['skus'])));
        }

        if ($primarySku === '' && $skus !== []) {
            $primarySku = $skus[0];
        }

        if ($primarySku === '') {
            $result->setData([
                'success' => false,
                'message' => __('Primary SKU is required for recommendations.')
            ]);
            return null;
        }

        return [
            'primary_sku' => $primarySku,
            'skus' => $skus
        ];
    }

    private function buildRecommendations(string $primarySku, array $originalSkus): array
    {
        $normalisedOriginal = $this->normaliseSkuArray($originalSkus);

        try {
            $recommendationSkus = $this->requestRecommendationSkus($primarySku);
            $recommendationSkus = $this->normaliseSkuArray($recommendationSkus);

            $recommendationSkus = array_values(array_filter(
                $recommendationSkus,
                static function (string $value) use ($primarySku): bool {
                    return strcasecmp($value, $primarySku) !== 0;
                }
            ));

            if ($recommendationSkus === []) {
                $recommendationSkus = array_slice($normalisedOriginal, 1);
            }

            if ($recommendationSkus === []) {
                return [];
            }

            $recommendationSkus = array_slice($recommendationSkus, 0, 8);
            $collection = $this->createProductCollection($recommendationSkus);
            if (!$collection->getSize()) {
                return [];
            }

            $html = $this->renderProductList(
                $collection,
                'ai.search.product.recommendations',
                'related',
                self::DISPLAY_MODE_SIDEBAR
            );
            $count = (int)$collection->getSize();

            $this->logger->debug('AI Search recommendations async', [
                'count' => $count,
                'skus' => $recommendationSkus
            ]);

            return [
                'html' => $html,
                'count' => $count,
                'title' => __('You might also like')
            ];
        } catch (Throwable $exception) {
            $this->logger->warning('Unable to build async AI recommendations', [
                'message' => $exception->getMessage()
            ]);
        }

        return [];
    }

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

    private function extractRecommendationSkus(array $payload): array
    {
        foreach ($this->iterateSkuCandidates($payload) as $skus) {
            if ($skus !== []) {
                return $this->normaliseSkuArray($skus);
            }
        }

        $this->logger->debug('Async recommendation payload ignored', [
            'keys' => array_keys($payload),
            'sample' => $this->extractSample($payload)
        ]);

        return [];
    }

    private function iterateSkuCandidates(array $payload): iterable
    {
        yield $this->extractFromSkusKey($payload);
        yield $this->extractFromDataKey($payload);
        yield $this->extractFromResultsKey($payload);
        yield $this->extractFromRecommendationsKey($payload);
        yield $this->extractFromRootList($payload);
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

    private function extractFromRecommendationsKey(array $payload): array
    {
        if (!isset($payload['recommendations']) || !is_array($payload['recommendations'])) {
            return [];
        }

        return $this->collectSkuValues($payload['recommendations'], ['product_sku', 'sku']);
    }

    private function extractFromRootList(array $payload): array
    {
        if (!array_is_list($payload)) {
            return [];
        }

        return $this->collectSkuValues($payload);
    }

    private function collectSkuValues(array $items, array $arrayKeys = ['sku', 'product_sku']): array
    {
        $skus = [];

        foreach ($items as $item) {
            $sku = $this->resolveSkuValue($item, $arrayKeys);
            if ($sku !== null) {
                $skus[] = $sku;
            }
        }

        return array_values($skus);
    }

    private function resolveSkuValue($item, array $arrayKeys): ?string
    {
        if (is_string($item)) {
            $item = trim($item);
            return $item === '' ? null : $item;
        }

        if (!is_array($item)) {
            return null;
        }

        foreach ($arrayKeys as $key) {
            if (!isset($item[$key])) {
                continue;
            }

            $value = trim((string)$item[$key]);
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

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

    private function renderProductList(
        Collection $collection,
        string $blockName = 'ai.search.product.recommendations',
        string $contextKey = 'related',
        string $displayMode = self::DISPLAY_MODE_SIDEBAR
    ): string {
        $contextKey = $this->normaliseContextKey($contextKey);
        $uniqueBlockName = sprintf('%s.%s', $blockName, $contextKey);

        $this->removeLayoutElement($uniqueBlockName);

        /** @var RecommendationList $productListBlock */
        $productListBlock = $this->layout->createBlock(
            RecommendationList::class,
            $uniqueBlockName
        );

        $productListBlock->setProductCollection($collection);
        $useFullTemplate = $displayMode === self::DISPLAY_MODE_WITH_ACTIONS;
        $template = $useFullTemplate
            ? 'Magento_Catalog::product/list.phtml'
            : 'Ecommerce66_AiSearch::widget/recommendations-sidebar.phtml';

        $productListBlock->setTemplate($template);
        $productListBlock->setData('mode', 'grid');
        $productListBlock->setData('viewModel', $this->productOptionsData);
        if ($useFullTemplate) {
            $productListBlock->setChild('addto', $this->createAddToContainer($contextKey));
            $productListBlock->setChild('toolbar', $this->createToolbarBlock($contextKey));

            $formKeyBlockName = sprintf('ai.search.product.formkey.%s', $contextKey);
            if (!$this->layout->getBlock($formKeyBlockName)) {
                $this->layout->createBlock(FormKey::class, $formKeyBlockName);
            }
        }

        return $productListBlock->toHtml();
    }

    private function createAddToContainer(string $contextKey): ProductListContainer
    {
        $containerName = sprintf('ai.search.product.addto.%s', $contextKey);
        $this->removeLayoutElement($containerName);

        /** @var ProductListContainer $container */
        $container = $this->layout->createBlock(
            ProductListContainer::class,
            $containerName
        );

        $compareName = sprintf('ai.search.product.compare.%s', $contextKey);
        $this->removeLayoutElement($compareName);

        /** @var CompareBlock $compareBlock */
        $compareBlock = $this->layout->createBlock(
            CompareBlock::class,
            $compareName
        );
        $compareBlock->setTemplate('Magento_Catalog::product/list/addto/compare.phtml');
        $container->setChild('compare', $compareBlock);

        $this->attachWishlistBlock($container, $contextKey);

        return $container;
    }

    private function attachWishlistBlock(ProductListContainer $container, string $contextKey): void
    {
        try {
            $wishlistName = sprintf('ai.search.product.wishlist.%s', $contextKey);
            $this->removeLayoutElement($wishlistName);

            /** @var WishlistBlock $wishlistBlock */
            $wishlistBlock = $this->layout->createBlock(
                WishlistBlock::class,
                $wishlistName
            );
            $wishlistBlock->setTemplate('Magento_Wishlist::catalog/product/list/addto/wishlist.phtml');
            $container->setChild('wishlist', $wishlistBlock);
        } catch (Throwable $exception) {
            $this->logger->info('Wishlist block not available for AI recommendations');
        }
    }

    private function createToolbarBlock(string $contextKey): ToolbarBlock
    {
        $toolbarName = sprintf('ai.search.product.toolbar.%s', $contextKey);
        $this->removeLayoutElement($toolbarName);

        /** @var ToolbarBlock $toolbarBlock */
        $toolbarBlock = $this->layout->createBlock(
            ToolbarBlock::class,
            $toolbarName
        );
        $toolbarBlock->setTemplate('Ecommerce66_AiSearch::widget/toolbar-empty.phtml');

        return $toolbarBlock;
    }

    private function normaliseSkuArray(array $items): array
    {
        $filtered = array_filter(array_map(
            static function ($value): string {
                return trim((string)$value);
            },
            $items
        ));

        return array_values(array_unique($filtered));
    }

    private function extractSample(array $payload)
    {
        foreach ($payload as $value) {
            if ($value !== null) {
                return is_array($value) ? array_slice($value, 0, 5, true) : $value;
            }
        }

        return null;
    }

    private function removeLayoutElement(string $name): void
    {
        if ($name === '' || !$this->layout->getBlock($name)) {
            return;
        }

        if (method_exists($this->layout, 'unsetElement')) {
            $this->layout->unsetElement($name);
        }
    }

    private function normaliseContextKey(string $contextKey): string
    {
        $key = strtolower(trim($contextKey));
        if ($key === '') {
            $key = 'context';
        }

        $key = preg_replace('/[^a-z0-9]+/', '-', $key) ?? 'context';
        $key = trim($key, '-');

        if ($key === '') {
            $key = 'context';
        }

        return $key;
    }
}
