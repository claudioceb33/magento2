<?php
declare(strict_types=1);

namespace Ceb\Installments\Model\ResourceModel\Installment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'installment_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Ceb\Installments\Model\Installment::class,
            \Ceb\Installments\Model\ResourceModel\Installment::class
        );
    }
}

