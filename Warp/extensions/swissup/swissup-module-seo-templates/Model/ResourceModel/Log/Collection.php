<?php

namespace Swissup\SeoTemplates\Model\ResourceModel\Log;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init(
            'Swissup\SeoTemplates\Model\Log',
            'Swissup\SeoTemplates\Model\ResourceModel\Log'
        );
    }
}
