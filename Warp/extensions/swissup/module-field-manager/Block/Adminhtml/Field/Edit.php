<?php
namespace Swissup\FieldManager\Block\Adminhtml\Field;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var string
     */
    protected $resource;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_blockGroup = $data['config']['blockGroup'];
        $this->resource = $data['config']['resource'];
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize field edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'attribute_id';
        $this->_controller = 'adminhtml_field';
        parent::_construct();
        if ($this->isAllowedAction($this->resource . '::save')) {
            $this->buttonList->update('save', 'label', __('Save Field'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }

        $field = $this->coreRegistry->registry('entity_attribute');
        if ($this->isAllowedAction($this->resource . '::delete') &&
            $field->getIsUserDefined()) {
            $this->buttonList->update('delete', 'label', __('Delete Field'));
        } else {
            $this->buttonList->remove('delete');
        }
    }

    /**
     * Retrieve text for header element depending on loaded post
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        $field = $this->coreRegistry->registry('entity_attribute');
        if ($field->getId()) {
            return __("Edit Field '%1'", $this->escapeHtml($field->getFrontendLabel()));
        } else {
            return __('New Field');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Retrieve URL for validation
     *
     * @return string
     */
    public function getValidationUrl()
    {
        return $this->getUrl('*/*/validate', ['_current' => true]);
    }

    /**
     * Return save url for edit form
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', ['_current' => true, 'back' => null]);
    }
}
