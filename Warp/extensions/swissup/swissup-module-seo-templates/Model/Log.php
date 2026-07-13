<?php

namespace Swissup\SeoTemplates\Model;

class Log extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Swissup\SeoTemplates\Model\ResourceModel\Log::class);
    }
}
