<?php

namespace Swissup\HreflangImportExport\Model\Export\CatalogCategory;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Data\Collection\AbstractDb as DbCollection;
use Swissup\HreflangImportExport\Model\Export\AbstractEntity\CollectionFactory as AbstractFactory;

class CollectionFactory extends AbstractFactory
{
    public function __construct(
        CategoryCollectionFactory $collectionFactory
    ) {
        parent::__construct($collectionFactory);
    }

    protected function joinHreflang(DbCollection $collection): void
    {
        $collection->getSelect()->joinLeft(
            ['h' => $collection->getTable('swissup_hreflang_category')],
            'h.category_id = e.entity_id',
            []
        );
        $collection->distinct(true);
        $collection->addFilterToMap('hreflang_links', 'h.hreflang_category_id');
    }
}
