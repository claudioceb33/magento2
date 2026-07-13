<?php
namespace Swissup\AddressFieldManager\Controller\Adminhtml\Index;

class Save extends \Swissup\FieldManager\Controller\Adminhtml\Index\Save
{
    const ADMIN_RESOURCE = 'Swissup_AddressFieldManager::save';

    const ENTITY_TYPE = \Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS;

    const USED_IN_FORMS = 'adminhtml_customer_address';

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Customer\Model\AttributeFactory $customerAttributeFactory,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Catalog\Model\Product\UrlFactory $urlFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Eav\Model\Config $eavModelConfig,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $eavAttributeSetFactory,
        \Swissup\FieldManager\Helper\Data $helper,
        \Swissup\AddressFieldManager\Model\ResourceModel\Address\OrderFactory $orderFactory,
        \Swissup\AddressFieldManager\Model\ResourceModel\Address\QuoteFactory $quoteFactory,
        \Magento\Framework\Validator\RegexFactory $regexValidatorFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->quoteFactory = $quoteFactory;
        parent::__construct(
            $context, $resultPageFactory, $registry, $eavEntityFactory,
            $customerAttributeFactory, $websiteFactory, $urlFactory,
            $resultJsonFactory, $eavModelConfig, $eavAttributeSetFactory,
            $helper, $regexValidatorFactory
        );
    }
}
