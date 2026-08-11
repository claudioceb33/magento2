<?php
namespace Ceb\Gtm\ViewModel;

use Ceb\Gtm\Helper\Data as GtmHelper;
use Magento\Catalog\Helper\Product\ProductList as ProductListHelper;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Catalog\Model\Product\ProductList\Toolbar as ToolbarModel;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class ProductList implements ArgumentInterface
{
    /**
     * @var int
     */
    protected const DEFAULT_LIMIT = 10;

    /**
     * @var LayerResolver
     */
    protected $layerResolver;

    /**
     * @var ProductListHelper
     */
    protected $productListHelper;

    /**
     * @var ToolbarModel
     */
    protected $toolbarModel;

    /**
     * @var GtmHelper
     */
    protected $gtmHelper;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Registry
     */
    protected $registry;

    public function __construct(
        LayerResolver $layerResolver,
        ProductListHelper $productListHelper,
        ToolbarModel $toolbarModel,
        GtmHelper $gtmHelper,
        RequestInterface $request,
        Registry $registry
    ) {
        $this->layerResolver = $layerResolver;
        $this->productListHelper = $productListHelper;
        $this->toolbarModel = $toolbarModel;
        $this->gtmHelper = $gtmHelper;
        $this->request = $request;
        $this->registry = $registry;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getFirstVisibleItems(int $limit = self::DEFAULT_LIMIT): array
    {
        return $this->getVisibleItemsData(
            $limit,
            [
                'use_visible_variant' => true,
                'include_list_metadata' => true,
            ]
        );
    }

    /**
     * @param int $limit
     * @return array<int, array<string, mixed>>
     */
    public function getViewItemListItems(int $limit = self::DEFAULT_LIMIT): array
    {
        return $this->getVisibleItemsData(
            $limit,
            [
                'use_visible_variant' => false,
                'include_list_metadata' => false,
            ]
        );
    }

    /**
     * @param int $limit
     * @param array<string, bool> $options
     * @return array<int, array<string, mixed>>
     */
    protected function getVisibleItemsData(int $limit, array $options): array
    {
        $collection = $this->getProductCollection();
        if (!$collection) {
            return [];
        }

        $this->prepareCollection($collection);

        return $this->collectVisibleItems(
            $collection,
            $this->getItemListName(),
            $limit,
            $options
        );
    }

    /**
     * @return mixed
     */
    protected function getProductCollection()
    {
        $layer = $this->layerResolver->get();
        $collection = $layer->getProductCollection();

        $this->applyToolbarState($collection);

        return $collection;
    }

    /**
     * Ensure the shared layer collection is paginated before any consumer loads it.
     *
     * @param mixed $collection
     * @return void
     */
    protected function applyToolbarState($collection): void
    {
        $collection->setCurPage($this->toolbarModel->getCurrentPage());
        $collection->setPageSize($this->resolveCurrentLimit());

        $currentOrder = $this->toolbarModel->getOrder() ?: $this->productListHelper->getDefaultSortField();
        if (!$currentOrder) {
            return;
        }

        $currentDirection = $this->toolbarModel->getDirection() ?: ProductListHelper::DEFAULT_SORT_DIRECTION;
        if ($currentOrder === 'position') {
            $collection->addAttributeToSort($currentOrder, $currentDirection);
            return;
        }

        $collection->setOrder($currentOrder, $currentDirection);
    }

    /**
     * @return int
     */
    protected function resolveCurrentLimit(): int
    {
        $viewMode = $this->toolbarModel->getMode() ?: 'grid';
        $availableLimits = $this->productListHelper->getAvailableLimit($viewMode);
        $currentLimit = $this->toolbarModel->getLimit();

        if (is_string($currentLimit) && isset($availableLimits[$currentLimit]) && ctype_digit($currentLimit)) {
            return (int) $currentLimit;
        }

        return $this->productListHelper->getDefaultLimitPerPageValue($viewMode);
    }

    /**
     * @param mixed $collection
     * @return void
     */
    protected function prepareCollection($collection): void
    {
        $brandAttribute = $this->gtmHelper->getBrandAttribute();
        if (!$brandAttribute) {
            return;
        }

        $collection->addAttributeToSelect($brandAttribute);
    }

    /**
     * @param mixed $collection
     * @param string $itemListName
     * @param int $limit
     * @param array<string, bool> $options
     * @return array<int, array<string, mixed>>
     */
    protected function collectVisibleItems(
        $collection,
        string $itemListName,
        int $limit,
        array $options
    ): array {
        $products = [];
        $index = 1;
        foreach ($collection as $product) {
            if ($index > $limit) {
                break;
            }

            $resolvedProduct = $options['use_visible_variant']
                ? $this->resolveVisibleProduct($product)
                : [
                    'product' => $product,
                    'variant' => null,
                ];

            $products[] = $this->buildItemData(
                $resolvedProduct['product'],
                $itemListName,
                $index,
                $resolvedProduct['variant'],
                $options['include_list_metadata']
            );
            $index++;
        }

        return $products;
    }

    public function getStoreCurrencyCode(): string
    {
        return $this->gtmHelper->getStoreCurrencyCode();
    }

    /**
     * @return string
     */
    public function getCurrentItemListName(): string
    {
        return $this->getItemListName();
    }

    /**
     * @param mixed $product
     * @return array{product:mixed,variant:?string}
     */
    protected function resolveVisibleProduct($product): array
    {
        $variant = null;
        if ($product->getTypeId() !== 'configurable') {
            return [
                'product' => $product,
                'variant' => $variant,
            ];
        }

        $children = $product->getTypeInstance()->getUsedProducts($product);
        foreach ($children as $child) {
            if (!$child->isSaleable()) {
                continue;
            }

            return [
                'product' => $child,
                'variant' => $child->getSku(),
            ];
        }

        return [
            'product' => $product,
            'variant' => $variant,
        ];
    }

    /**
     * @param mixed $product
     * @param string $itemListName
     * @param int $index
     * @param string|null $variant
     * @param bool $includeListMetadata
     * @return array<string, mixed>
     */
    protected function buildItemData(
        $product,
        string $itemListName,
        int $index,
        ?string $variant,
        bool $includeListMetadata
    ): array {
        $itemData = [
            'item_id' => $product->getSku(),
            'item_name' => $product->getName(),
            'price' => (float) $product->getFinalPrice(),
            'index' => $index,
        ];

        if ($includeListMetadata) {
            $itemData['item_list_name'] = $itemListName;
            $itemData['item_list_id'] = $product->getCategoryId();
        }

        $brand = $this->gtmHelper->getProductBrand($product);
        if ($brand) {
            $itemData['item_brand'] = $brand;
        }

        $category = $this->gtmHelper->getProductCategory($product);
        if ($category) {
            $itemData['item_category'] = $category;
        }

        if (!$variant) {
            return $itemData;
        }

        $itemData['item_variant'] = $variant;
        return $itemData;
    }

    /**
     * @return string
     */
    protected function getItemListName(): string
    {
        $fullActionName = $this->request->getFullActionName();
        if ($fullActionName === 'catalogsearch_result_index') {
            return 'Search Results';
        }

        if ($fullActionName !== 'catalog_category_view') {
            return 'Product List';
        }

        $category = $this->registry->registry('current_category');
        if (!$category) {
            return 'Category Listing';
        }

        return 'Category: ' . $category->getName();
    }
}
