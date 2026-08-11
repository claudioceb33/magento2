<?php
namespace Ceb\Gtm\Model;

use Magento\Framework\Model\AbstractModel;

class GaData extends AbstractModel
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_ORPHAN = 'orphan';

    protected function _construct()
    {
        $this->_init(\Ceb\Gtm\Model\ResourceModel\GaData::class);
    }
}
