<?php

namespace Swissup\SeoUrls\Model\Attribute;

class Advanced extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Swissup\SeoUrls\Model\ResourceModel\Attribute\Advanced::class);
    }
}
