<?php
namespace Swissup\FieldManager\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Api\CustomerMetadataInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $inputTypesMap = [
        'date' => [
            'backend_model' => \Magento\Eav\Model\Entity\Attribute\Backend\Datetime::class
        ],
        'select' => [
            'source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class
        ],
        'multiselect' => [
            'backend_model' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
            'source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class
        ],
        'boolean' => [
            'source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class
        ]
    ];

    protected $columnTypesMap = [
        'datetime' => [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE
        ],
        'decimal' => [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            'length' => '12,4'
        ],
        'int' => [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
        ],
        'text' => [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT
        ],
        'varchar' => [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'length' => 255
        ]
    ];

    protected $templateMap = [
        'text' => 'Swissup_FieldManager::field/text.phtml',
        'textarea' => 'Swissup_FieldManager::field/textarea.phtml',
        'select' => 'Swissup_FieldManager::field/select.phtml',
        'boolean' => 'Swissup_FieldManager::field/select.phtml',
        'multiselect' => 'Swissup_FieldManager::field/select.phtml',
        'date' => 'Swissup_FieldManager::field/date.phtml'
    ];

    protected $displayTypes = [
        'select' => [
            'checkbox-set' => 'Swissup_FieldManager::field/checkbox-set.phtml',
            'select' => 'Swissup_FieldManager::field/select.phtml',
        ],
        'boolean' => [
            'checkbox-set' => 'Swissup_FieldManager::field/checkbox-set.phtml',
            'select' => 'Swissup_FieldManager::field/select.phtml',
        ],
        'multiselect' => [
            'checkbox-set' => 'Swissup_FieldManager::field/checkbox-set.phtml',
            'select' => 'Swissup_FieldManager::field/select.phtml',
        ],
    ];

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    protected \Magento\Framework\App\RequestFactory $requestFactory;

    protected \Magento\Customer\Model\Metadata\ElementFactory $elementFactory;

    protected \Magento\Customer\Api\AddressMetadataInterface $addressMetadata;

    protected $attributesMemo = [];

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\RequestFactory $requestFactory,
        \Magento\Customer\Model\Metadata\ElementFactory $elementFactory,
        \Magento\Customer\Api\AddressMetadataInterface $addressMetadata
    ) {
        $this->eavConfig = $eavConfig;
        $this->requestFactory = $requestFactory;
        $this->elementFactory = $elementFactory;
        $this->addressMetadata = $addressMetadata;
        parent::__construct($context);
    }

    /**
     * @param string $type
     * @return string|null
     */
    public function getColumnType($type)
    {
        if (!empty($this->columnTypesMap[$type])) {
            return $this->columnTypesMap[$type];
        }

        return null;
    }

    /**
     * @param string $type
     * @return string|null
     */
    public function getBackendModelByInput($type)
    {
        if (!empty($this->inputTypesMap[$type]['backend_model'])) {
            return $this->inputTypesMap[$type]['backend_model'];
        }

        return null;
    }

    /**
     * @param string $type
     * @return string|null
     */
    public function getSourceModelByInput($type)
    {
        if (!empty($this->inputTypesMap[$type]['source_model'])) {
            return $this->inputTypesMap[$type]['source_model'];
        }

        return null;
    }

    public function addScopeHtml($block, $elementIds)
    {
        $fieldObject = $block->getFieldObject();
        if ($fieldObject->getWebsite()->getId() &&
            $fieldObject->getWebsite()->getId() == $block->getRequest()->getParam('website')) {
            foreach ($elementIds as $elementId) {
                $element = $block->getForm()->getElement($elementId);
                if ($element->getDisabled()) continue;

                $id = $element->getId();
                if (strncmp($id, 'default_value_', strlen('default_value_')) === 0) {
                    $id = 'default_value';
                }
                $isDefault = $fieldObject->getData('scope_' . $id) === null;
                if ($isDefault) $element->setDisabled(true);

                $html = $block->getLayout()->createBlock(
                    \Magento\Backend\Block\Template::class
                )->setTemplate(
                    'Swissup_FieldManager::form/scope.phtml'
                )->setData([
                    'element' => $element,
                    'is_default' => $isDefault
                ])->toHtml();

                $element->setAfterElementHtml($html);
            }
        }
    }

    /**
     * Get list of custom attribute codes
     * @param  string $entityType
     * @return array
     */
    public function getCustomAttributeCodes(
        $entityType = \Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS
    ) {
        return array_keys($this->getCustomAttributes($entityType));
    }

    public function getCustomAttributes(
        $entityType = \Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS
    ) {
        if ($this->attributesMemo) {
            return $this->attributesMemo;
        }

        foreach ($this->getEntityAttributes($entityType) as $attribute) {
            if ($attribute->getIsUserDefined()) {
                $this->attributesMemo[$attribute->getAttributeCode()] = $attribute;
            }
        }

        return $this->attributesMemo;
    }

    /**
     * Get list of attributes by entity
     * @param  string $entityType
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute[]
     */
    public function getEntityAttributes(
        $entityType = \Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS
    ) {
        return $this->eavConfig->getEntityAttributes($entityType);
    }

    public function getFieldTemplate($field)
    {
        $inputType = $field->getFrontendInput();
        $displayType = $this->getFieldDisplayType($field);

        if (isset($this->displayTypes[$inputType][$displayType])) {
            return $this->displayTypes[$inputType][$displayType];
        }

        return $this->templateMap[$inputType] ?? null;
    }

    public function getFieldDisplayType($field)
    {
        preg_match(
            '/sfm-(select|checkbox-set)/',
            (string) $field->getFrontendClass(),
            $displayType
        );

        return $displayType ? $displayType[1] : '';
    }

    /**
     * Get list of forms available for entity
     * @return array
     */
    public function getUsedInForms()
    {
        return [
            'customer_address' => [
                ['label' => __('Customer Address Registration'), 'value' => 'customer_register_address'],
                ['label' => __('Customer Account Address'), 'value' => 'customer_address_edit']
            ],
            'customer' => [
                ['label' => __('Customer Registration'), 'value' => 'customer_account_create'],
                ['label' => __('Customer Account Edit'), 'value' => 'customer_account_edit'],
                ['label' => __('Admin Checkout'), 'value' => 'adminhtml_checkout']
            ]
        ];
    }

    /**
     * Check if field is ignored in config
     *
     * @param  $field
     * @param  string $entityType
     * @return boolean
     */
    public function isFieldIgnored($field, $entityType)
    {
        // only customer fields can be ignored
        if ($entityType == CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER) {
            $ignoredFields = (string) $this->scopeConfig->getValue(
                'customer_field_manager/compatibility/ignore',
                ScopeInterface::SCOPE_STORE
            );

            return in_array($field->getAttributeCode(), explode(',', $ignoredFields));
        }

        return false;
    }

    /**
     * Remove attribute code from value in Magento 2.4
     *
     * @param  $address
     * @return array|null
     */
    public function fixAttributesValues($address)
    {
        $customAttributes = $this->getCustomAttributes();
        if (!$customAttributes) {
            return null;
        }

        $customAttributesData = [];
        foreach ($customAttributes as $code => $attribute) {
            $inputType = $attribute->getFrontendInput();
            $value = $address->getData($code);
            if (!$value) {
                continue;
            }

            // remove attr code (Magento 2.4 bugfix)
            $valueParts = preg_split('/\r\n|\r|\n/', $value, -1);
            if ($valueParts[0] === $code) {
                array_shift($valueParts);
            }

            if (count($valueParts)) {
                $glue = $inputType === 'multiselect' ? ',' : PHP_EOL;
                $value = implode($glue, $valueParts);
            } else {
                $value = null;
            }

            // fix date value when using french locale
            if ($value && $inputType === 'date') {
                $dataModel = $this->elementFactory->create(
                    $this->addressMetadata->getAttributeMetadata($code),
                    $value,
                    \Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                );
                $request = $this->requestFactory->create();
                $request->setParams([
                    $code => $value,
                ]);
                $value = $dataModel->extractValue($request);
            }

            $customAttributesData[$code] = $value;
        }

        return !empty($customAttributesData) ? $customAttributesData : null;
    }
}
