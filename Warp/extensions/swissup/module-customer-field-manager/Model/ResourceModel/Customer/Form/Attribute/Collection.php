<?php
namespace Swissup\CustomerFieldManager\Model\ResourceModel\Customer\Form\Attribute;

use Magento\Customer\Api\CustomerMetadataInterface;

class Collection extends \Swissup\FieldManager\Model\ResourceModel\Customer\Form\Attribute\Collection
{
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->setEntityType(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER)
            ->addFormCodeFilter('adminhtml_customer');

        return $this;
    }
}
