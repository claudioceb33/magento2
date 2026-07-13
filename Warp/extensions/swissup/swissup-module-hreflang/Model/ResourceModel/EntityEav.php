<?php

namespace Swissup\Hreflang\Model\ResourceModel;

use Magento\Catalog\Model\ResourceModel\AbstractResource;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

abstract class EntityEav
{
    protected array $statusData = [];
    protected AbstractResource $resource;
    protected StoreManagerInterface $storeManager;
    protected string $statusAttributeCode;

    public function __construct(
        AbstractResource $resource,
        StoreManagerInterface $storeManager,
        string $statusAttributeCode = ''
    ) {
        $this->resource = $resource;
        $this->storeManager = $storeManager;
        $this->statusAttributeCode = $statusAttributeCode;
    }

    /**
     * @param  DataObject $entity
     * @param  Store      $store
     * @return boolean
     */
    abstract public function isEnabled(DataObject $entity, Store $store): bool;

    /**
     * @param  array $items
     * @return $this
     */
    public function preloadStatusData(
        array $items,
        bool $isPreloadAll = false
    ): self {
        $attribute = $this->getStatusAttribute();
        $linkField = $attribute->getEntity()->getLinkField();

        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(
                $attribute->getBackend()->getTable(),
                [$linkField, 'store_id', 'value']
            )
            ->where('attribute_id = :attribute_id');

        if (!$isPreloadAll) {
            $ids = array_map(function ($item) use ($linkField) {
                return $item->getData($linkField) ?: $item->getId();
            }, $items);
            $select->where("{$linkField} in (?)", $ids);
        }

        $data = $connection->fetchAll(
            $select,
            ['attribute_id' => $attribute->getId()]
        );
        foreach ($data as $row) {
            $key = $row[$linkField];
            $storeId = (int)$row['store_id'];
            $value = $row['value'];
            if (isset($this->statusData[$key])) {
                $this->statusData[$key][$storeId] = $value;
            } else {
                $this->statusData[$key] = [
                    $storeId => $value
                ];
            }
        }

        return $this;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    public function getStatusAttribute()
    {
        return $this->resource->getAttribute($this->statusAttributeCode);
    }

    public function getAllStoreviewsId(): ?int
    {
        try {
            $adminStore = $this->storeManager->getStore('admin');
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return (int)$adminStore->getId();
    }
}
