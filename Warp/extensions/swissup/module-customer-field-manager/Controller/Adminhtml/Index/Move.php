<?php
namespace Swissup\CustomerFieldManager\Controller\Adminhtml\Index;

class Move extends \Swissup\FieldManager\Controller\Adminhtml\Index\Move
{
    const ADMIN_RESOURCE = 'Swissup_CustomerFieldManager::save';
    const ENTITY_CODE = \Magento\Customer\Api\CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER;
}
