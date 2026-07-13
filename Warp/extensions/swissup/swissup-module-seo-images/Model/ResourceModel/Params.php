<?php

namespace Swissup\SeoImages\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Params extends AbstractDb
{
    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_seoimages_params', 'params_id');
    }
}
