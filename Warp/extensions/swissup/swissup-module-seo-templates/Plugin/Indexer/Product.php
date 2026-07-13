<?php

namespace Swissup\SeoTemplates\Plugin\Indexer;

use Magento\Catalog\Model\ResourceModel\Product as ResourceProduct;
use Magento\Framework\Model\AbstractModel;

class Product extends AbstarctPlugin
{
    const INDEXER_ID = 'swissup_seotemplates_product';

    /**
     * Reindex on product save.
     *
     * @param  ResourceProduct $productResource
     * @param  ResourceProduct $result
     * @param  AbstractModel   $product
     * @return ResourceProduct
     */
    public function afterSave(
        ResourceProduct $productResource,
        ResourceProduct$result,
        AbstractModel $product
    ) {
        $this->reindexRow($product->getEntityId());

        return $result;
    }

    /**
     * Reindex on product delete.
     *
     * @param  ResourceProduct $productResource
     * @param  ResourceProduct $result
     * @param  AbstractModel   $product
     * @return ResourceProduct
     */
    public function afterDelete(
        ResourceProduct $productResource,
        ResourceProduct $result,
        AbstractModel $product
    ) {
        $this->reindexRow($product->getEntityId());

        return $result;
    }

    /**
     * @return string
     */
    public function getIndexId()
    {
        return self::INDEXER_ID;
    }
}
