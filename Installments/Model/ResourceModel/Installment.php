<?php
declare(strict_types=1);

namespace Ceb\Installments\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Installment extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('ceb_installments_installment', 'installment_id');
    }
}

