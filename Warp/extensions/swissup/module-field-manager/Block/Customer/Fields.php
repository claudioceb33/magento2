<?php
namespace Swissup\FieldManager\Block\Customer;

use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\View\Element\Html\Date;
use Magento\Framework\View\Element\Template;

abstract class Fields extends Template
{
    const ENTITY_TYPE = '';

    private AttributeMetadataDataProvider $attributeMetadataDataProvider;

    private \Magento\Framework\Data\Form\FilterFactory $filterFactory;

    private \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory;

    private \Swissup\FieldManager\Helper\Data $helper;

    protected $customerData = null;

    protected ProductMetadataInterface $productMetadata;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        AttributeMetadataDataProvider $attributeMetadataDataProvider,
        \Magento\Framework\Data\Form\FilterFactory $filterFactory,
        \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory,
        \Swissup\FieldManager\Helper\Data $helper,
        ProductMetadataInterface $productMetadata,
        array $data = []
    ) {
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->filterFactory = $filterFactory;
        $this->yesnoFactory = $yesnoFactory;
        $this->helper = $helper;
        $this->productMetadata = $productMetadata;
        parent::__construct($context, $data);
    }

    public function getFields(): array
    {
        $fields = $this->attributeMetadataDataProvider->loadAttributesCollection(
            static::ENTITY_TYPE,
            $this->getData('formCode')
        );

        $result = [];
        $storeId = $this->_storeManager->getStore()->getId();
        foreach ($fields as $field) {
            if (!$this->canDisplayField($field) || !$field->getIsVisible()) {
                continue;
            }

            $block = $this->getLayout()->createBlock(Template::class);
            $block->setTemplate($this->helper->getFieldTemplate($field));
            $block->addData([
                'label' => $field->getStoreLabel($storeId),
                'field_name' => $field->getAttributeCode() . ($field->getFrontendInput() === 'multiselect' ? '[]' : ''),
                'html_id' => 'field_' . $field->getAttributeCode(),
                'html_class' => $field->getIsRequired() == 1 ? 'required-entry' : '',
                'additional_classes' => $this->getAdditionalClasses($field),
                'options' => $this->getFieldOptions($field),
                'value' => $this->getFieldValue($field),
                'field' => $field,
            ]);

            if ($field->getFrontendInput() === 'date') {
                $block->setDateField(
                    $this->getLayout()->createBlock(Date::class)
                        ->setId($block->getHtmlId())
                        ->setName($block->getFieldName())
                        ->setValue($block->getValue())
                        ->setDateFormat($this->_localeDate->getDateFormatWithLongYear())
                        ->setShowOn('both')
                        ->setChangeMonth(true)
                        ->setChangeYear(true)
                );
            }

            $result[] = $block;
        }

        return $result;
    }

    /**
     * Check if field can be displayed
     * @param  $field
     * @return boolean
     */
    protected function canDisplayField($field)
    {
        return $field->getIsUserDefined() &&
               $this->helper->getFieldTemplate($field) &&
               !$this->helper->isFieldIgnored($field, static::ENTITY_TYPE);
    }

    /**
     * @param  $field
     * @return string
     */
    protected function getAdditionalClasses($field)
    {
        $classes = 'field-' . str_replace('_', '-', $field->getAttributeCode());

        if ($field->getIsRequired() == 1) {
            $classes .= ' required';
        }

        if ($field->getFrontendInput() == 'multiselect') {
            $classes .= ' multiple';
        }

        return trim($classes);
    }

    /**
     * @param  $field
     * @return string|null
     */
    protected function getFieldValue($field)
    {
        $value = null;
        if ($this->getCustomerData()) {
            $fieldValueObject = $this->customerData
                ->getCustomAttribute($field->getAttributeCode());
            $value = $fieldValueObject ? $fieldValueObject->getValue() : null;
        }

        if ($value === null) {
            $value = $field->getDefaultValue();
            if ($field->getFrontendInput() == 'multiselect' && $value) {
                $value = explode(',', $value);
            }
        }

        if ($value && $field->getFrontendInput() == 'date') {
            $filter = $this->filterFactory->create('date', [
                'format' => $this->_localeDate->getDateFormatWithLongYear()
            ]);
            $value = $filter->outputFilter($value);
        }

        return $value;
    }

    protected function getCustomerData()
    {
        return null;
    }

    /**
     * @param  $field
     * @param  int $storeId
     * @return array
     */
    protected function getFieldOptions($field)
    {
        if ($field->getFrontendInput() == 'date') {
            $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
            $options = [
                'dateFormat' => $dateFormat
            ];
        } elseif ($field->getFrontendInput() == 'boolean') {
            $options = $this->yesnoFactory->create()->toOptionArray();
        } else {
            $options = [];
            $optionsArr = $field->getOptions();
            foreach ($optionsArr as $option) {
                $options[] = [
                    'value' => trim($option->getValue()),
                    'label' => trim($option->getLabel()),
                ];
            }
        }

        return $options;
    }

    /**
     * Show block only for Magento Community
     * @return bool
     */
    public function canShow()
    {
        return $this->productMetadata->getEdition() == 'Community';
    }
}
