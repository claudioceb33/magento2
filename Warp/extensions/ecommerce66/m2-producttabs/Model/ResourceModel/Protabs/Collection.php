<?php

namespace Ecommerce66\ProductTabs\Model\ResourceModel\Protabs;

/**
 * Protabs resource model collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Init resource collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ecommerce66\ProductTabs\Model\Protabs', 'Ecommerce66\ProductTabs\Model\ResourceModel\Protabs');
    }
}
