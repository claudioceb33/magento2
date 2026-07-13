<?php
namespace Swissup\CustomerFieldManager\Controller\Adminhtml\Index;

class Edit extends \Swissup\FieldManager\Controller\Adminhtml\Index\Edit
{
    const ADMIN_RESOURCE = 'Swissup_CustomerFieldManager::save';
    const ACTIVE_MENU = 'Swissup_CustomerFieldManager::index';
    const TITLE = 'Customer Fields Manager';
    const EDIT_TITLE = 'Edit Customer Field';
    const NEW_TITLE = 'New Customer Field';
    const ENTITY_TYPE = \Magento\Customer\Api\CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER;
}
