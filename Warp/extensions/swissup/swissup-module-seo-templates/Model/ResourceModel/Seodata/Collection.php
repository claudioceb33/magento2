<?php

namespace Swissup\SeoTemplates\Model\ResourceModel\Seodata;

use Magento\Store\Model\Store;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * init collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Swissup\SeoTemplates\Model\Seodata',
            'Swissup\SeoTemplates\Model\ResourceModel\Seodata'
        );
    }

    /**
     * Add store id filter
     *
     * @param int|array $storeId
     */
    public function addStoreFilter($storeId)
    {
        if (!is_array($storeId)) {
            $stores = [$storeId];
        } else {
            $stores = $storeId;
        }

        $stores[] = Store::DEFAULT_STORE_ID;
        $this->addFilter('store_id', array('in' => $stores), 'public');
        return $this;
    }
}
