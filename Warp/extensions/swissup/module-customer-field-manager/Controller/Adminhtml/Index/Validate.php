<?php
namespace Swissup\CustomerFieldManager\Controller\Adminhtml\Index;

class Validate extends \Swissup\FieldManager\Controller\Adminhtml\Index\Validate
{
    const ADMIN_RESOURCE = 'Swissup_CustomerFieldManager::index';
    const RESERVED_ATTRIBUTES = [];
    const ENTITY_TYPE = \Magento\Customer\Api\CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER;
}
