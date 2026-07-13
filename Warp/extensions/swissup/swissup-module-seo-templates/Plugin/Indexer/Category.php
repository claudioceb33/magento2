<?php

namespace Swissup\SeoTemplates\Plugin\Indexer;

use Magento\Catalog\Model\ResourceModel\Category as ResourceCategory;
use Magento\Framework\Model\AbstractModel;

class Category extends AbstarctPlugin
{
    const INDEXER_ID = 'swissup_seotemplates_category';

    /**
     * Reindex on category save.
     *
     * @param  ResourceCategory $categoryResource
     * @param  ResourceCategory $result
     * @param  AbstractModel    $category
     * @return ResourceCategory
     */
    public function afterSave(
        ResourceCategory $categoryResource,
        ResourceCategory $result,
        AbstractModel $category
    ) {
        $this->reindexRow($category->getEntityId());

        return $result;
    }

    /**
     * Reindex on category delete.
     *
     * @param  ResourceCategory $categoryResource
     * @param  ResourceCategory $result
     * @param  AbstractModel    $category
     * @return ResourceCategory
     */
    public function afterDelete(
        ResourceCategory $categoryResource,
        ResourceCategory $result,
        AbstractModel $category
    ) {
        $this->reindexRow($category->getEntityId());

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
