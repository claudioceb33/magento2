<?php

namespace Swissup\SeoImages\Model;

class Entity extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Swissup\SeoImages\Model\ResourceModel\Entity::class);
    }
}
