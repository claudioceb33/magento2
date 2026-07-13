<?php
namespace Swissup\FieldManager\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Eav\Api\AttributeRepositoryInterface as AttributeRepository;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Swissup\FieldManager\Helper\BackendGrid;
use Swissup\FieldManager\Model\Config\Source\Status;

class MassStatus extends \Magento\Backend\App\Action
{
    const KEY_WEBSITE = '';
    const ENTITY_CODE = '';
    const USED_IN_FORMS = '';

    /** @var AttributeRepository  */
    protected $attributeRepository;

    /** @var JsonFactory  */
    protected $jsonFactory;

    /**
     * @var \Swissup\FieldManager\Helper\Data
     */
    protected $helper;

    /**
     * @var BackendGrid
     */
    private $gridHelper;

    /**
     * @var Filter
     */
    private $filter;

    private $collectionFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param AttributeRepository $attributeRepository
     * @param Config $config
     * @param BackendGrid $gridHelper
     * @param \Swissup\FieldManager\Helper\Data $helper
     * @param mixed $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        AttributeRepository $attributeRepository,
        Config $config,
        BackendGrid $gridHelper,
        \Swissup\FieldManager\Helper\Data $helper,
        $collectionFactory = null
    ) {
        parent::__construct($context);
        $this->gridHelper = $gridHelper;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->attributeRepository = $attributeRepository;
        $this->config = $config;
        $this->helper = $helper;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Customer\Model\ResourceModel\Form\Attribute\Collection $collection */
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $websiteId  = $this->gridHelper->getCurrentWebsiteId(static::KEY_WEBSITE);
        $status     = $this->getRequest()->getParam('status');

        /** @var \Magento\Eav\Model\Attribute $attribute */
        foreach ($collection as $attribute) {
            try {
                $this->saveAttribute($attribute, $status, $websiteId);
                $this->syncConfiguration($attribute, $status, $websiteId);
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    $this->getErrorWithAttributeId($attribute, __($e->getMessage())
                ));
            }
        }

        $this->messageManager->addSuccess(
            __('A total of %1 record(s) have been updated.', $collection->getSize())
        );

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/', [
            'website' => $websiteId
        ]);
    }

    /**
     * Convert status key into is_required and is_visible
     *
     * @param \Magento\Eav\Model\Attribute $attribute
     * @param  array $values
     * @return array
     */
    private function prepareAttributeValues($attribute, $values)
    {
        $mapping = [
            Status::HIDDEN   => ['is_required' => 0, 'is_visible' => 0],
            Status::REQUIRED => ['is_required' => 1, 'is_visible' => 1],
            Status::OPTIONAL => ['is_required' => 0, 'is_visible' => 1],
            'delete'         => ['scope_is_required' => null, 'scope_is_visible' => null],
        ];
        $values = array_merge($values, $mapping[$values['status']]);

        $attributeCode = $attribute->getAttributeCode();

        if (in_array($attributeCode, ['firstname', 'lastname', 'email', 'website_id', 'group_id'])) {
            $values['is_required'] = 1;
            $values['is_visible'] = 1;
        } elseif (in_array($attributeCode, ['region', 'region_id'])) {
            $values['is_required'] = 0;
            $values['is_visible'] = 1;
        } elseif ($attributeCode === 'country_id') {
            $values['is_visible'] = 1;
        }

        if ($this->isScopeUsed()) {
            if ($values['status'] !== 'delete') {
                $values['scope_is_required'] = $values['is_required'];
                $values['scope_is_visible'] = $values['is_visible'];
            }
            unset($values['is_required']);
            unset($values['is_visible']);
        }

        unset($values['status']);

        return $values;
    }

    /**
     * @param \Magento\Eav\Model\Attribute $attribute
     * @param string $status
     * @param int $websiteId
     * @return void
     */
    private function saveAttribute($attribute, $status, $websiteId = null)
    {
        $values = $this->prepareAttributeValues($attribute, [
            'status' => $status
        ]);

        // fix for required field validation
        if ($status == Status::REQUIRED) {
            $availableForms = $this->helper->getUsedInForms()[static::ENTITY_CODE];
            $formsArr = array_column($availableForms, 'value');
            $formsArr[] = static::USED_IN_FORMS;
            $values['used_in_forms'] = $formsArr;
        }

        $attribute = $this->attributeRepository->get(
            static::ENTITY_CODE, $attribute->getAttributeCode()
        )
        ->addData($values)
        ->setWebsite($websiteId);

        // commented because it saves attribute on global level only
        // $this->attributeRepository->save($attribute);
        $attribute->save();

        // sync region and region_id status
        if ($attribute->getAttributeCode() === 'region') {
            $attribute = $this->attributeRepository
                ->get(static::ENTITY_CODE, 'region_id')
                ->addData($values)
                ->setWebsite($websiteId);

            $attribute->save();
        }
    }

    /**
     * @param \Magento\Eav\Model\Attribute $attribute
     * @param string $status
     * @param int $websiteId
     */
    private function syncConfiguration($attribute, $status, $websiteId)
    {
        $attributeCode = $attribute->getAttributeCode();
        $paths = [
            'vat_id'     => 'customer/create_account/vat_frontend_visibility',
            'prefix'     => 'customer/address/prefix_show',
            'suffix'     => 'customer/address/suffix_show',
            'middlename' => 'customer/address/middlename_show',
            'telephone'  => 'customer/address/telephone_show',
            'company'    => 'customer/address/company_show',
            'fax'        => 'customer/address/fax_show',
            'tax_vat'    => 'customer/address/taxvat_show',
            'dob'        => 'customer/address/dob_show',
            'gender'     => 'customer/address/gender_show',
        ];
        if (!isset($paths[$attributeCode])) {
            return;
        }

        $scope = 'default';
        $scopeId = 0;
        if ($websiteId) {
            $scope = 'websites';
            $scopeId = $websiteId;
        }

        $valueMapping = [
            Status::HIDDEN   => '',
            Status::REQUIRED => 'req',
            Status::OPTIONAL => 'opt',
        ];
        if (!isset($valueMapping[$status])) {
            $this->config->deleteConfig($paths[$attributeCode], $scope, $scopeId);
        } else {
            $value = $valueMapping[$status];

            if (in_array($attributeCode, ['vat_id', 'middlename'])) {
                $value = $value ? 1 : 0;
            }

            $this->config->saveConfig($paths[$attributeCode], $value, $scope, $scopeId);
        }
    }

    /**
     * Add attribute code to error message
     *
     * @param AttributeInterface $attribute
     * @param string $errorText
     * @return string
     */
    private function getErrorWithAttributeId(AttributeInterface $attribute, $errorText)
    {
        return '[Attribute ID: ' . $attribute->getAttributeCode() . '] ' . $errorText;
    }

    /**
     * @return bool
     */
    private function isScopeUsed()
    {
        return (bool) $this->gridHelper->getCurrentWebsiteId(static::KEY_WEBSITE);
    }
}
