<?php

namespace Swissup\HreflangImportExport\Model\Export\CmsPage;

use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Magento\Framework\Data\Collection\AbstractDb as DbCollection;
use Swissup\HreflangImportExport\Model\Export\AbstractEntity\CollectionFactory as AbstractFactory;

class CollectionFactory extends AbstractFactory
{
    public function __construct(
        PageCollectionFactory $collectionFactory
    ) {
        parent::__construct($collectionFactory);
    }

    protected function joinHreflang(DbCollection $collection): void
    {
        $collection->getSelect()->joinLeft(
            ['h' => $collection->getTable('swissup_hreflang_cms')],
            'h.page_id = main_table.page_id',
            []
        );
        $collection->distinct(true);
        $collection->addFilterToMap('hreflang_links', 'h.hreflang_page_id');
    }
}
