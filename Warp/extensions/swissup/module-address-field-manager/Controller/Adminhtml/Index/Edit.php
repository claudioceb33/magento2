<?php
namespace Swissup\AddressFieldManager\Controller\Adminhtml\Index;

class Edit extends \Swissup\FieldManager\Controller\Adminhtml\Index\Edit
{
    const ADMIN_RESOURCE = 'Swissup_AddressFieldManager::save';
    const ACTIVE_MENU = 'Swissup_AddressFieldManager::index';
    const TITLE = 'Address Fields Manager';
    const EDIT_TITLE = 'Edit Address Field';
    const NEW_TITLE = 'New Address Field';
    const ENTITY_TYPE = \Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS;
}
