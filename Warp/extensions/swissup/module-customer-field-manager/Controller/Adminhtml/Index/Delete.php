<?php
namespace Swissup\CustomerFieldManager\Controller\Adminhtml\Index;

class Delete extends \Swissup\FieldManager\Controller\Adminhtml\Index\Delete
{
    const ADMIN_RESOURCE = 'Swissup_CustomerFieldManager::delete';

    const ENTITY_TYPE = \Magento\Customer\Api\CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER;
}
