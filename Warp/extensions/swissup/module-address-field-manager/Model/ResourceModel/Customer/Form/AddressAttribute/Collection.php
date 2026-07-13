<?php
namespace Swissup\AddressFieldManager\Model\ResourceModel\Customer\Form\AddressAttribute;

use Magento\Customer\Api\AddressMetadataInterface;

class Collection extends \Swissup\FieldManager\Model\ResourceModel\Customer\Form\Attribute\Collection
{
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->setEntityType(AddressMetadataInterface::ENTITY_TYPE_ADDRESS)
            ->addFormCodeFilter('adminhtml_customer_address');

        return $this;
    }
}
