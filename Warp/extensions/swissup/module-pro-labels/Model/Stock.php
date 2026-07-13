<?php

namespace Swissup\ProLabels\Model;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ObjectManager;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\Framework\Module\Manager as ModuleManager;

class Stock
{
    /**
     * @var array
     */
    private $cachedStockData = [];

    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    protected $criteriaFactory;

    /**
     * @var StockItemRepositoryInterface
     */
    protected $stockItemRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @param StockConfigurationInterface       $stockConfiguration
     * @param StockItemCriteriaInterfaceFactory $criteriaFactory
     * @param StockItemRepositoryInterface      $stockItemRepository
     * @param StoreManagerInterface             $storeManager
     * @param ResourceConnection                $resource
     * @param ModuleManager                     $moduleManager
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        StockItemCriteriaInterfaceFactory $criteriaFactory,
        StockItemRepositoryInterface $stockItemRepository,
        StoreManagerInterface $storeManager,
        ResourceConnection $resource,
        ModuleManager $moduleManager
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->criteriaFactory = $criteriaFactory;
        $this->stockItemRepository = $stockItemRepository;
        $this->storeManager = $storeManager;
        $this->resource = $resource;
        $this->moduleManager = $moduleManager;
    }

    /**
     * Get stock quantoty for product
     *
     * @param  ProductInterface $product
     * @return
     */
    public function getQty(
        ProductInterface $product,
        string $aggregateFunction = 'min'
    ): float {
        $ids = $this->getChildIds($product);
        if (!$ids) {
            $ids = [$product->getId()];
        }

        $qty = 0;
        $qtys = $this->_getStockQty($ids);
        if (!$qtys) {
            return $qty;
        }

        switch ($aggregateFunction) {
            case 'min':
                $qty = min(array_values($qtys));
                break;

            case 'max':
                $qty = max(array_values($qtys));
                break;

            default:
                $qty = (float)reset($qtys);
                break;
        }

        return $qty;
    }

    /**
     * Get stock status for product
     *
     * @param  ProductInterface $product
     * @return bool
     * @deprecated 1.6.16
     */
    public function getIsInStock(ProductInterface $product)
    {
        return $this->isInStock($product);
    }

    public function isInStock(ProductInterface $product): bool
    {
        $ids = $this->getChildIds($product) ?: [$product->getId()];
        $stockData = $this->_getStockData($ids, $product->getStoreId());
        $isInStock = array_reduce($stockData, function ($inStock, $item) {
            if ($item->getIsInStock()) {
                $inStock = true;
            }

            return $inStock;
        }, false);

        return $isInStock;
    }

    public function isOutOfStock(ProductInterface $product): bool
    {
        $ids = $this->getChildIds($product) ?: [$product->getId()];
        $stockData = $this->_getStockData($ids, $product->getStoreId());
        $isOutOfStock = array_reduce($stockData, function ($isOutOfStock, $item) {
            if ($item->getIsInStock()) {
                $isOutOfStock = false;
            }

            return $isOutOfStock;
        }, true);

        return $isOutOfStock;
    }

    /**
     * Get backorders enabled for product.
     *
     * @param  ProductInterface $product
     * @return boolean
     */
    public function getIsBackorders(ProductInterface $product)
    {
        $ids = $this->getChildIds($product);
        if (!$ids) {
            $ids = [$product->getId()];
        }

        $stockData = $this->_getStockData($ids, $product->getStoreId());
        $isBackorders = array_reduce($stockData, function ($status, $item) {
            if ($item->getBackorders()) {
                $status = true;
            }

            return $status;
        }, false);

        return $isBackorders;
    }

    /**
     * Get manage stock status for product.
     *
     * @param  ProductInterface $product
     * @return boolean
     */
    public function isManageStock(ProductInterface $product)
    {
        $ids = $this->getChildIds($product);
        if (!$ids) {
            $ids = [$product->getId()];
        }

        $stockData = $this->_getStockData($ids, $product->getStoreId());
        $isManageStock = array_reduce($stockData, function ($status, $item) {
            if ($item->getIsManageStock()) {
                $status = true;
            }

            return $status;
        }, false);

        return $isManageStock;
    }

    /**
     * Get ids of child products
     *
     * @param  ProductInterface $product
     * @return array
     */
    public function getChildIds(ProductInterface $product)
    {
        $ids = [];
        if ('grouped' === $product->getTypeId()) {
            $ids = $product->getTypeInstance()->getAssociatedProductIds($product);
        } elseif ('bundle' === $product->getTypeId()) {
            $optionIds = $product->getTypeInstance()->getOptionsIds($product);
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $simpleProducts */
            $simpleProducts = $product->getTypeInstance()->getSelectionsCollection($optionIds, $product);
            $ids = $simpleProducts->getAllIds();
        }

        return $ids;
    }

    /**
     * Preload (warm) stock data for collection (array) of products
     *
     * @param  ProductInterface[]  $products
     * @return $this
     */
    public function preloadStockForProducts(array $products)
    {
        $ids = [];
        foreach ($products as $product) {
            $childIds = $this->getChildIds($product);
            $childIds = $childIds ?: [$product->getId()];
            $ids = array_merge($ids, $childIds);
        }

        $this->_getStockData($ids);

        return $this;
    }

    /**
     * Get stock quantities using product ids
     *
     * @param  array $productIds
     * @return array
     */
    private function _getStockQty(array $productIds)
    {
        $stockData = $this->_getStockData($productIds);

        return array_map(function ($stockItem) {
            return $stockItem->getQty();
        }, $stockData);
    }

    /**
     * @param  array $productIds
     * @return array
     */
    private function _getStockData(array $productIds, $storeId = null)
    {
        $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();
        if (!isset($this->cachedStockData[$websiteId])) {
            $this->cachedStockData[$websiteId] = [];
        }
        // Make sure productIds array has only INTEGER or STRING values
        $productIds = array_filter($productIds, 'is_scalar');
        if (array_diff($productIds, array_keys($this->cachedStockData[$websiteId]))) {
            $reservation = [];
            $inventory = [];
            if ($this->moduleManager->isOutputEnabled('Magento_InventorySales')) {
                $objectManager = ObjectManager::getInstance();
                $stockByWebsiteId = $objectManager->get(StockByWebsiteIdResolverInterface::class);
                $stockId = (int)$stockByWebsiteId->execute($websiteId)->getStockId();
                $reservation = $this->_getReservationData($productIds, $stockId);
                $inventory = $this->_getInventoryStockData($productIds, $stockId);
            }

            $items = $inventory ?: $this->_getCatalogInventoryData($productIds);
            foreach ($items as $item) {
                $id = $item->getProductId();
                $this->cachedStockData[$websiteId][$id] = new \Magento\Framework\DataObject([
                    'is_manage_stock' => $item->getManageStock(),
                    /**
                     * (Code below is inspired by
                     * \Magento\InventorySales\Model\GetProductSalableQty::execute)
                     */
                    'qty' => $item->getQty()
                        + ($reservation[$id]['qty'] ?? 0)
                        - $item->getMinQty(),
                    'is_in_stock' => $item->getIsInStock(),
                    'backorders' => $item->getBackorders()
                ]);
            }
        }

        return array_intersect_key($this->cachedStockData[$websiteId], array_flip($productIds));
    }

    /**
     * Get reservation data from MSI modules
     *
     * @param  array  $productIds
     * @param  int    $stockId
     * @return array
     */
    private function _getReservationData(array $productIds, int $stockId)
    {
        $connection = $this->resource->getConnection();
        $reservationTable = $this->resource->getTableName('inventory_reservation');
        $productTable = $this->resource->getTableName('catalog_product_entity');

        $select = $connection->select()
            ->from(['p' => $productTable], ['p.entity_id'])
            ->joinLeft(['r' => $reservationTable], 'p.sku = r.sku' ,['qty' => 'SUM(r.quantity)'])
            ->where('p.entity_id IN (?)', $productIds)
            ->where('r.stock_id = ?', $stockId)
            ->group('p.entity_id');

        return $connection->fetchAssoc($select);
    }

    /**
     * @param  array  $productIds
     * @param  int    $stockId
     * @return array
     */
    private function _getInventoryStockData(array $productIds, int $stockId)
    {
        $connection = $this->resource->getConnection();
        $productTable = $this->resource->getTableName('catalog_product_entity');
        $stockItemTable = $this->resource->getTableName('cataloginventory_stock_item');
        $sourceItemTable = $this->resource->getTableName('inventory_source_item');
        $stockLinkTable = $this->resource->getTableName('inventory_source_stock_link');
        $salesChanelTable = $this->resource->getTableName('inventory_stock_sales_channel');

        $select = $connection->select()
            ->from(['p' => $productTable], ['p.entity_id'])
            ->joinLeft(['si' => $sourceItemTable], 'si.sku = p.sku', ['si.quantity', 'si.status'])
            ->joinLeft(['sl' => $stockLinkTable], 'sl.source_code = si.source_code', [])
            ->joinLeft(
                ['st' => $stockItemTable],
                'st.product_id = p.entity_id AND st.stock_id = sl.stock_id',
                ['manage_stock', 'use_config_manage_stock', 'backorders', 'use_config_backorders']
            )
            ->where('p.entity_id IN (?)', $productIds)
            ->where('sl.stock_id = ?', $stockId);

        $data = $connection->fetchAll($select);
        $configBackorders = $this->getConfigBackorders();
        $configManageStock = $this->getConfigManageStock();

        return array_reduce($data, function ($collected, $rawData) use ($configManageStock, $configBackorders) {
            $id = $rawData['entity_id'];
            $item = $collected[$id] ?? new \Magento\Framework\DataObject([
                'product_id' => $id
            ]);

            if ($rawData['status'] === '1') {
                $item->setQty($item->getQty() + $rawData['quantity']);
                $item->setIsInStock(true);
            }
            $item->setManageStock(
                $rawData['use_config_manage_stock'] ?
                    $configManageStock :
                    (int)$rawData['manage_stock']
            );
            $item->setBackorders(
                $rawData['use_config_backorders'] ?
                    $configBackorders :
                    (int)$rawData['backorders']
            );

            $collected[$id] = $item;

            return $collected;
        }, []);
    }

    /**
     * @param  array  $productIds
     * @return array
     */
    private function _getCatalogInventoryData(array $productIds)
    {
        $criteria = $this->criteriaFactory->create();
        $criteria->setScopeFilter($this->stockConfiguration->getDefaultScopeId());
        $criteria->setProductsFilter($productIds);

        return $this->stockItemRepository->getList($criteria)->getItems();
    }

    /**
     * @param  null|string|bool|int|\Magento\Store\Model\Store $store
     * @return int
     */
    public function getConfigBackorders($store = null) {
        $store = $store ?: $this->storeManager->getStore();

        return (int)$this->stockConfiguration->getBackorders($store);
    }

    /**
     * @param  null|string|bool|int|\Magento\Store\Model\Store $store
     * @return int
     */
    public function getConfigManageStock($store = null) {
        $store = $store ?: $this->storeManager->getStore();

        return (int)$this->stockConfiguration->getManageStock($store);
    }

    /**
     * @param  string  $mode
     * @return boolean
     */
    public function isStockProlabelEnabled($mode)
    {
        $store = $this->storeManager->getStore();
        $isEnabled = !!$store->getConfig("prolabels/in_stock/{$mode}/active")
            || !!$store->getConfig("prolabels/out_stock/{$mode}/active");

        return $isEnabled;
    }
}
