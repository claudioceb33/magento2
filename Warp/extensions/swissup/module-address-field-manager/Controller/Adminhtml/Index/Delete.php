<?php
namespace Swissup\AddressFieldManager\Controller\Adminhtml\Index;

class Delete extends \Swissup\FieldManager\Controller\Adminhtml\Index\Delete
{
    const ADMIN_RESOURCE = 'Swissup_AddressFieldManager::delete';

    const ENTITY_TYPE = \Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS;

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
        \Swissup\AddressFieldManager\Model\ResourceModel\Address\QuoteFactory $quoteFactory,
        \Swissup\AddressFieldManager\Model\ResourceModel\Address\OrderFactory $orderFactory,
        \Magento\Framework\Validator\RegexFactory $regexValidatorFactory
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->orderFactory = $orderFactory;
        parent::__construct(
            $context, $resultPageFactory, $registry, $eavEntityFactory,
            $customerAttributeFactory, $websiteFactory, $urlFactory,
            $resultJsonFactory, $eavModelConfig, $eavAttributeSetFactory,
            $helper, $regexValidatorFactory
        );
    }
}
