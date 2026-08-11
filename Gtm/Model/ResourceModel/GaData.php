<?php
namespace Ceb\Gtm\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class GaData extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('ceb_gtm_ga_data', 'entity_id');
    }
}
