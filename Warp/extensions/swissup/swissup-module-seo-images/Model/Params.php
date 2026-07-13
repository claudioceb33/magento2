<?php

namespace Swissup\SeoImages\Model;

class Params extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Swissup\SeoImages\Model\ResourceModel\Params::class);
    }
}
