<?php
namespace Swissup\AddressFieldManager\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Swissup\AddressFieldManager\Model\ResourceModel\Customer\Form\AddressAttribute\CollectionFactory;
use Magento\Eav\Api\AttributeRepositoryInterface as AttributeRepository;
use Magento\Config\Model\ResourceModel\Config;
use Swissup\FieldManager\Helper\BackendGrid;

class MassStatus extends \Swissup\FieldManager\Controller\Adminhtml\Index\MassStatus
{
    const ADMIN_RESOURCE = 'Swissup_AddressFieldManager::save';
    const ENTITY_CODE = \Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS;
    const KEY_WEBSITE = 'swissup_address_field_manager_website';
    const USED_IN_FORMS = 'adminhtml_customer_address';

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
