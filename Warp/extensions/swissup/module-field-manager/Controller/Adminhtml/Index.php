<?php
namespace Swissup\FieldManager\Controller\Adminhtml;

use Magento\Framework\Validator\RegexFactory;

abstract class Index extends \Magento\Backend\App\Action
{
    const ENTITY_TYPE = '';

    private $websiteFactory;
    /**
     * @var string
     */
    protected $entityTypeId;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * Entity factory
     *
     * @var \Magento\Eav\Model\EntityFactory
     */
    protected $eavEntityFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Customer\Model\AttributeFactory
     */
    protected $customerAttributeFactory;

    /**
     * @var \Magento\Catalog\Model\Product\UrlFactory
     */
    protected $urlFactory;

     /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavModelConfig;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $eavAttributeSetFactory;

    /**
     * @var \Swissup\FieldManager\Helper\Data
     */
    protected $helper;

    /**
     * @var RegexFactory
     */
    protected $regexValidatorFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Customer\Model\AttributeFactory $customerAttributeFactory
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Catalog\Model\Product\UrlFactory $urlFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Eav\Model\Config $eavModelConfig
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $eavAttributeSetFactory
     * @param \Swissup\FieldManager\Helper\Data $helper
     * @param RegexFactory $regexValidatorFactory
     */
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
        RegexFactory $regexValidatorFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $registry;
        $this->eavEntityFactory = $eavEntityFactory;
        $this->customerAttributeFactory = $customerAttributeFactory;
        $this->websiteFactory = $websiteFactory;
        $this->urlFactory = $urlFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->eavModelConfig = $eavModelConfig;
        $this->eavAttributeSetFactory = $eavAttributeSetFactory;
        $this->helper = $helper;
        $this->regexValidatorFactory = $regexValidatorFactory;
        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $this->entityTypeId = (int)$this->eavEntityFactory->create()->setType(
            static::ENTITY_TYPE
        )->getTypeId();

        return parent::dispatch($request);
    }

    protected function initModel()
    {
        $model = $this->customerAttributeFactory->create()
        ->setWebsite(
            $this->getRequest()->getParam('website') ?: $this->websiteFactory->create()
        );

        return $model;
    }

    /**
     * Generate code from label
     *
     * @param string $label
     * @return string
     */
    protected function generateCode($label)
    {
        $code = substr(
            preg_replace(
                '/[^a-z_0-9]/',
                '_',
                $this->urlFactory->create()->formatUrlKey($label)
            ),
            0,
            30
        );

        $validatorAttrCode = $this->regexValidatorFactory->create(['pattern' => '/^[a-z][a-z_0-9]{0,29}[a-z0-9]$/']);
        if (!$validatorAttrCode->isValid($code)) {
            $code = 'attr_' . ($code ?: substr(sha1(time()), 0, 8));
        }

        return $code;
    }
}
