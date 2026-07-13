<?php

namespace Swissup\SeoImages\Model\ResourceModel\Index;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Swissup\SeoImages\Model\Index::class,
            \Swissup\SeoImages\Model\ResourceModel\Index::class
        );
    }
}
