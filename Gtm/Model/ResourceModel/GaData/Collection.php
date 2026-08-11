<?php
namespace Ceb\Gtm\Model\ResourceModel\GaData;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Ceb\Gtm\Model\GaData::class,
            \Ceb\Gtm\Model\ResourceModel\GaData::class
        );
    }
}
