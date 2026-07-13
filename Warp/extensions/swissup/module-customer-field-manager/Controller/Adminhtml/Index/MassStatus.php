<?php
namespace Swissup\CustomerFieldManager\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Swissup\CustomerFieldManager\Model\ResourceModel\Customer\Form\Attribute\CollectionFactory;
use Magento\Eav\Api\AttributeRepositoryInterface as AttributeRepository;
use Magento\Config\Model\ResourceModel\Config;
use Swissup\FieldManager\Helper\BackendGrid;

class MassStatus extends \Swissup\FieldManager\Controller\Adminhtml\Index\MassStatus
{
    const ADMIN_RESOURCE = 'Swissup_CustomerFieldManager::save';
    const ENTITY_CODE = \Magento\Customer\Api\CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER;
    const KEY_WEBSITE = 'swissup_customer_field_manager_website';
    const USED_IN_FORMS = 'adminhtml_customer';

    public function __construct(
        Context $context,
        Filter $filter,
        AttributeRepository $attributeRepository,
        Config $config,
        BackendGrid $gridHelper,
        \Swissup\FieldManager\Helper\Data $helper,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct(
            $context, $filter, $attributeRepository,
            $config, $gridHelper, $helper, $collectionFactory
        );
    }
}
