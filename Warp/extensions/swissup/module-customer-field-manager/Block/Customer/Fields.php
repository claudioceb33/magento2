<?php
namespace Swissup\CustomerFieldManager\Block\Customer;

class Fields extends \Swissup\FieldManager\Block\Customer\Fields
{
    const ENTITY_TYPE = \Magento\Customer\Api\CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER;

    protected function getCustomerData() {
        if (!$this->customerData) {
            $customerEditBlock = $this->getLayout()->getBlock('customer_edit');
            if ($customerEditBlock) {
                $this->customerData = $customerEditBlock->getCustomer();
            }
        }

        return $this->customerData;
    }
}
