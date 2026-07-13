<?php
namespace Swissup\FieldManager\Block\Adminhtml\Field\Edit\Tab;

class Front extends \Swissup\FieldManager\Block\Adminhtml\Field\Edit\Tab\AbstractTab
{
    private $entityType;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory,
        \Swissup\FieldManager\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory,
        \Magento\Eav\Helper\Data $eavData,
        \Swissup\FieldManager\Helper\Data $helper,
        array $data = []
    ) {
        $this->yesnoSource = $yesnoFactory->create()->toOptionArray();
        $this->inputTypeFactory = $inputTypeFactory;
        $this->eavData = $eavData;
        $this->helper = $helper;
        $this->entityType = $data['config']['entityType'];
        parent::__construct(
            $context, $registry, $formFactory, $yesnoFactory,
            $inputTypeFactory, $eavData, $helper, $data
        );
    }

    /**
     * {@inheritdoc}
     * @return $this
     */
    protected function _prepareForm()
    {
        $fieldObject = $this->getFieldObject();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset(
            'front_fieldset',
            ['legend' => __('Storefront Properties')]
        );

        $fieldset->addField(
            'is_visible',
            'select',
            [
                'name' => $fieldObject->getWebsite()->getId() ? 'scope_is_visible' : 'is_visible',
                'label' => __('Show on Storefront'),
                'title' => __('Show on Storefront'),
                'values' => $this->yesnoSource,
                'scope_label' => '[website]'
            ]
        )->setDisabled($fieldObject->getIsSystem())->setIsSystem($fieldObject->getIsSystem());

        $fieldset->addField(
            'sort_order',
            'text',
            [
                'name' => 'sort_order',
                'label' => __('Sort Order'),
                'title' => __('Sort Order'),
                'required' => true,
                'class' => 'validate-digits',
                'scope_label' => '[global]'
            ]
        );

        $fieldset->addField(
            'used_in_forms',
            'multiselect',
            [
                'name' => 'used_in_forms',
                'label' => __('Show In Forms'),
                'title' => __('Show In Forms'),
                'values' => $this->helper->getUsedInForms()[$this->entityType],
                'value' => $fieldObject->getUsedInForms(),
                'can_be_empty' => true,
                'scope_label' => '[global]'
            ]
        )
        ->setSize(3)
        ->setDisabled(
            $fieldObject->getId() && (
                $fieldObject->getIsSystem() || (
                    !$fieldObject->getIsSystem() &&
                    !$fieldObject->getIsUserDefined()
                )
            )
        )->setIsSystem($fieldObject->getIsSystem());

        $this->setForm($form);
        $form->setValues($fieldObject->getData());
        $this->helper->addScopeHtml($this, ['is_visible']);

        return parent::_prepareForm();
    }
}
