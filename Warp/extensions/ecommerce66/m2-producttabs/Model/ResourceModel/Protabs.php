<?php

namespace Ecommerce66\ProductTabs\Model\ResourceModel;

class Protabs extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize connection and table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mgs_protabs', 'tab_id');
    }
}
