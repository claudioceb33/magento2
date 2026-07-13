<?php
namespace Swissup\AddressFieldManager\Controller\Adminhtml\Index;

class Validate extends \Swissup\FieldManager\Controller\Adminhtml\Index\Validate
{
    const ADMIN_RESOURCE = 'Swissup_AddressFieldManager::index';
    const RESERVED_ATTRIBUTES = ['address_type'];
    const ENTITY_TYPE = \Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS;
}
