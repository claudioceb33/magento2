<?php
namespace Swissup\AddressFieldManager\Block\Address;

class Edit extends \Swissup\FieldManager\Block\Customer\Fields
{
    const ENTITY_TYPE = \Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS;

    protected function getCustomerData() {
        if (!$this->customerData) {
            $customerEditBlock = $this->getLayout()->getBlock('customer_address_edit');
            if ($customerEditBlock) {
                $this->customerData = $customerEditBlock->getAddress();
            }
        }

        return $this->customerData;
    }
}
