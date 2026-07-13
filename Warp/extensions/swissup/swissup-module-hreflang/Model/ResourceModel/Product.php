<?php

namespace Swissup\Hreflang\Model\ResourceModel;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product as ResourceProduct;
use Magento\Framework\DataObject;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Product extends EntityEav
{
    private Status $status;

    public function __construct(
        Status $status,
        ResourceProduct $resource,
        StoreManagerInterface $storeManager
    ) {
        $this->status = $status;
        parent::__construct($resource, $storeManager, 'status');
    }

    /**
     * @param  DataObject $product
     * @param  Store      $store
     * @return boolean
     */
    public function isEnabled(DataObject $product, Store $store): bool
    {
        $attribute = $this->getStatusAttribute();
        $linkField = $attribute->getEntity()->getLinkField();
        $linkValue = $product->getData($linkField);
        $data = $this->statusData[$linkValue] ??
            ($this->preloadStatusData([$product])->statusData[$linkValue] ?? []);
        $allStoreviewsId = $this->getAllStoreviewsId();
        $status = $data[(int)$store->getId()] ?? ($data[$allStoreviewsId] ?? null);

        return in_array($status, $this->status->getVisibleStatusIds());
    }
}
