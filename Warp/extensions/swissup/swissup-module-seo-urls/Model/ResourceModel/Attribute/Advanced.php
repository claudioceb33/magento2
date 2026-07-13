<?php

namespace Swissup\SeoUrls\Model\ResourceModel\Attribute;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Advanced extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected $_isPkAutoIncrement = false;

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_seourls_attribute_advanced', 'attribute_id');
    }
}
