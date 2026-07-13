<?php
namespace Swissup\CustomerFieldManager\Controller\Adminhtml\Index;

class Save extends \Swissup\FieldManager\Controller\Adminhtml\Index\Save
{
    const ADMIN_RESOURCE = 'Swissup_CustomerFieldManager::save';

    const ENTITY_TYPE = \Magento\Customer\Api\CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER;

    const USED_IN_FORMS = 'adminhtml_customer';
}
