<?php

namespace Swissup\SeoTemplates\Model;

use Magento\Catalog\Model\AbstractModel;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Store\Model\StoreManagerInterface;
use Swissup\SeoTemplates\Model\Template;
use Swissup\SeoTemplates\Model\Generator\Assistant;

class SeodataBuilder
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var array
     */
    private $cache = [];

    private $collectionFactory;


    public function __construct(
        ResourceModel\Seodata\CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Get array with metadata (with memoization).
     *
     * @param  int    $entityId
     * @param  string $entityType
     * @return array
     */
    public function get($entityId, $entityType): array
    {
        $collected = $this->_get([$entityId], $entityType);

        return reset($collected);
    }

    /**
     * Private getter for generated metadata.
     *
     * @param  array  $entityIds
     * @param  int    $entityType
     * @return array
     */
    private function _get(array $entityIds, $entityType)
    {
        $this->cache[$entityType] = $this->cache[$entityType] ?? [];
        if (array_diff($entityIds, array_keys($this->cache[$entityType]))) {
            $collection = $this->collectionFactory->create()
                ->addStoreFilter($this->storeManager->getStore()->getId())
                ->addFieldToFilter('entity_id', ['in' => $entityIds])
                ->addFieldToFilter('entity_type', $entityType)
                ->setOrder('entity_id')
                ->setOrder('store_id', \Magento\Framework\Data\Collection::SORT_ORDER_DESC);
            $collection->each('unserialize');
            foreach ($entityIds as $entityId) {
                $this->cache[$entityType][$entityId] =
                    $collection->getItemsByColumnValue('entity_id', $entityId);
            }
        }

        return $entityIds
            ? array_intersect_key($this->cache[$entityType], array_flip($entityIds))
            : [];
    }

    /**
     * Preload (warm up) generated metadata.
     *
     * @param  array  $entityIds
     * @param  int    $entityType
     * @return $this
     */
    public function preload(array $entityIds, $entityType)
    {
        $this->_get($entityIds, $entityType);

        return $this;
    }

    /**
     * Get validated value by data key.
     *
     * @param int $storeId - If passed, this will be set to store ID of value.
     */
    public function getValidatedByKey(
        $key,
        AbstractModel $entity,
        int &$storeId = null
    ): string {
        $entityId = $entity->getId();
        $items = $entityId ?
            $this->get($entityId, Assistant::getEntityType($entity)) :
            [];

        // $items array with 1 or 2 items.
        // When there are 2 items in array: [0] storeview level; [1] global scope.
        // When there is 1 item in array: [0] storeview level / global scope.
        foreach ($items as $item) {
            $metadata = $item->getMetadata();
            $storeId = $item->getStoreId();
            $value = $metadata[$key]['value'] ?? trim((string)($metadata[$key] ?? ''));
            $conditionals = $metadata[$key]['conditional'] ?? [];
            foreach ($conditionals as $conditional) {
                $condition = $conditional['condition'] ?? false;
                if (!$condition) {
                    continue;
                }

                if ($condition->validate($entity)) {
                    $value = trim($conditional['value'] ?? '');
                }

                if ($value) {
                    // exit condition loop on fisrt valid condition and non-empty value;
                    return $value;
                }
            }

            if ($value) {
                // exit items loop on fisrt non-empty value;
                return $value;
            }
        }

        $storeId = null;

        return '';
    }
}
