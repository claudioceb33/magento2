<?php
namespace Swissup\FieldManager\Block\Adminhtml\Field\Edit\Tab;

use Swissup\FieldManager\Model\Adminhtml\System\Config\Source\InputtypeFactory;

class AbstractTab extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Customer\Model\Attribute
     */
    protected $field = null;

    /**
     * @var array
     */
    protected $yesnoSource;

    /**
     * @var InputtypeFactory
     */
    protected $inputTypeFactory;

    /**
     * Eav data
     *
     * @var \Magento\Eav\Helper\Data
     */
    protected $eavData = null;

    /**
     * @var \Swissup\FieldManager\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory
     * @param InputtypeFactory $inputTypeFactory
     * @param \Magento\Eav\Helper\Data $eavData
     * @param \Swissup\FieldManager\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory,
        InputtypeFactory $inputTypeFactory,
        \Magento\Eav\Helper\Data $eavData,
        \Swissup\FieldManager\Helper\Data $helper,
        array $data = []
    ) {
        $this->yesnoSource = $yesnoFactory->create()->toOptionArray();
        $this->inputTypeFactory = $inputTypeFactory;
        $this->eavData = $eavData;
        $this->helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Set field object
     *
     * @param \Magento\Customer\Model\Attribute $field
     * @return $this
     */
    public function setFieldObject($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Return field object
     *
     * @return \Magento\Customer\Model\Attribute
     */
    public function getFieldObject()
    {
        if (null === $this->field) {
            return $this->_coreRegistry->registry('entity_attribute');
        }

        return $this->field;
    }

    /**
     * Initialize form fields values
     *
     * @return $this
     */
    protected function _initFormValues()
    {
        $fieldObject = $this->getFieldObject();
        if ($fieldObject->getId()) {
            $this->getForm()->addValues($fieldObject->getData());
        }
        $result = parent::_initFormValues();

        $formValues = $fieldObject->getData();
        foreach (array_keys($formValues) as $idx) {
            $formValues[$idx] = $fieldObject->getDataUsingMethod($idx);
        }
        $this->getForm()->addValues($formValues);

        return $result;
    }
}
