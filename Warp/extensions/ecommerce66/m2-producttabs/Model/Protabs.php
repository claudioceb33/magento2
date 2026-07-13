<?php
// @ coding Standards IgnoreFile

namespace Ecommerce66\ProductTabs\Model;

class Protabs extends \Magento\Framework\Model\AbstractModel
{

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ecommerce66\ProductTabs\Model\ResourceModel\Protabs');
    }
}
