<?php
namespace Swissup\AddressFieldManager\Controller\Adminhtml\Index;

class Move extends \Swissup\FieldManager\Controller\Adminhtml\Index\Move
{
    const ADMIN_RESOURCE = 'Swissup_AddressFieldManager::save';
    const ENTITY_CODE = \Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS;
}
