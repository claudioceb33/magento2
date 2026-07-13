<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_Formbuilder
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\Formbuilder\Block\Adminhtml\Form\Edit\Tab;

use Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\Template\Config;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject as ObjectConverter;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;

class Main extends Generic implements TabInterface
{
    /**
     * @var Store
     */
    protected Store $systemStore;

    /**
     * @var GroupRepositoryInterface
     */
    protected GroupRepositoryInterface $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var ObjectConverter
     */
    protected ObjectConverter $objectConverter;
    protected CollectionFactory $templatesFactory;
    protected Config $emailConfig;

    /**
     * Main constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param GroupRepositoryInterface $groupRepository
     * @param ObjectConverter $objectConverter
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Store $systemStore
     * @param CollectionFactory $templatesFactory
     * @param Config $emailConfig
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        GroupRepositoryInterface $groupRepository,
        ObjectConverter $objectConverter,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Store $systemStore,
        CollectionFactory $templatesFactory,
        Config $emailConfig,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->groupRepository = $groupRepository;
        $this->objectConverter = $objectConverter;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->templatesFactory = $templatesFactory;
        $this->emailConfig = $emailConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('formbuilder_form');

        if ($this->isAllowedAction('Lof_Formbuilder::form_edit')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }
        $this->_eventManager->dispatch(
            'lof_check_license',
            ['obj' => $this,'ex' => 'Lof_Formbuilder']
        );
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('form_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Form Information')]);
        if ($model->getId()) {
            $fieldset->addField('form_id', 'hidden', ['name' => 'form_id']);
        }

        $fieldset->addField(
            'title',
            'text',
            [
                'name'     => 'title',
                'label'    => __('Form Title'),
                'title'    => __('Form Title'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'identifier',
            'text',
            [
                'name'     => 'identifier',
                'label'    => __('Identifier'),
                'title'    => __('Identifier'),
                'required' => true,
                'class'    => 'validate-xml-identifier',
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'email_receive',
            'text',
            [
                'name'     => 'email_receive',
                'label'    => __('Receive Notification Email'),
                'title'    => __('Receive Notification Email'),
                'disabled' => $isElementDisabled,
                'class' => 'validate-emails',
                'note'     => __(
                    'If you use multiple separate by comma. Note:
                    when sending to many email the load time will increase'
                )
            ]
        );

        $fieldset->addField(
            'tags',
            'text',
            [
                'name'     => 'tags',
                'label'    => __('Input the tags'),
                'title'    => __('Input the tags'),
                'disabled' => $isElementDisabled,
                'note'     => __(
                    'If you use multiple separate by comma. The tags key will been show on
                    email content at footer. Example: #your_tag_code1 #your_tag_code2 ...'
                )
            ]
        );

        $collection = $this->templatesFactory->create();
        $collection->load();
        $options = $collection->toOptionArray();
        $templateId = str_replace('/', '_', 'formbuilder/email/notify_template');
        $templateLabel = $this->emailConfig->getTemplateLabel($templateId);
        $templateLabel = __('%1 (Default)', $templateLabel);
        array_unshift($options, ['value' => $templateId, 'label' => $templateLabel]);
        $emailTemplates = [];
        foreach ($options as $k => $v) {
            $emailTemplates[$v['value']] = $v['label'];
        }
        $fieldset->addField(
            'email_template',
            'select',
            [
                'label'    => __('Email Message Template'),
                'title'    => __('Email Message Template'),
                'name'     => 'email_template',
                'options'  => $emailTemplates,
                'disabled' => $isElementDisabled
            ]
        );

        $thankoufields = [];
        $thankoufields[0] = '-----';
        $fieldset->addField(
            'thankyou_field',
            'select',
            [
                'label'    => __('Thankyou Field'),
                'title'    => __('Thankyou Field'),
                'name'     => 'thankyou_field',
                'options'  => $thankoufields,
                'disabled' => $isElementDisabled,
                'note'     => __(
                    'After submitting form successfully. A "thank you" email
                    automatically send to thank you field in which customers have just submitted'
                ),
                'after_element_html' => '
                <script>
                    require(["jquery"], function($) {
                        jQuery(document).ready(function($) {
                            $("#form_thankyou_field").attr("data-value", "' . $model->getThankyouField() . '");
                        });
                    });
                </script>'
            ]
        );

        $collection = $this->templatesFactory->create();
        $collection->load();
        $options = $collection->toOptionArray();
        $templateId = str_replace('/', '_', 'formbuilder/email/thankyou_template');
        $templateLabel = $this->emailConfig->getTemplateLabel($templateId);
        $templateLabel = __('%1 (Default)', $templateLabel);
        array_unshift($options, ['value' => $templateId, 'label' => $templateLabel]);
        $emailTemplates = [];
        foreach ($options as $k => $v) {
            $emailTemplates[$v['value']] = $v['label'];
        }
        $fieldset->addField(
            'thankyou_email_template',
            'select',
            [
                'label'    => __('Thankyou Email Template'),
                'title'    => __('Thankyou Email Template'),
                'name'     => 'thankyou_email_template',
                'options'  => $emailTemplates,
                'disabled' => $isElementDisabled
            ]
        );

        $senderEmailfields = [];
        $senderEmailfields[0] = '-----';
        $fieldset->addField(
            'sender_email_field',
            'select',
            [
                'label'    => __('Sender Email Field'),
                'title'    => __('Sender Email Field'),
                'name'     => 'sender_email_field',
                'options'  => $senderEmailfields,
                'disabled' => $isElementDisabled,
                'note'     => __(
                    'Choose the sender email field to allow module send email from the email address
                    field on the form, it will override the default sender on module settings.
                    Empty to use default sender on module settings.'
                ),
                'after_element_html' => '
                <script>
                    require(["jquery"], function($) {
                        jQuery(document).ready(function($) {
                            $("#form_sender_email_field").attr("data-value", "' . $model->getSenderEmailField() . '");
                        });
                    });
                </script>'
            ]
        );

        $senderNamefields = [];
        $senderNamefields[0] = '-----';
        $fieldset->addField(
            'sender_name_field',
            'select',
            [
                'label'    => __('Sender Name Field'),
                'title'    => __('Sender Name Field'),
                'name'     => 'sender_name_field',
                'options'  => $senderNamefields,
                'disabled' => $isElementDisabled,
                'note'     => __(
                    'Choose the sender name field to allow module send name from the name field on the form,
                    it will override the default sender on module settings.
                    Empty to use default sender on module settings.'
                ),
                'after_element_html' => '
                <script>
                    require(["jquery"], function($) {
                        jQuery(document).ready(function($) {
                            $("#form_sender_name_field").attr("data-value", "' . $model->getSenderNameField() . '");
                        });
                    });
                </script>'
            ]
        );

        $fieldset->addField(
            'submit_button_text',
            'text',
            [
                'name'     => 'submit_button_text',
                'label'    => __('Submit Button Text'),
                'title'    => __('Submit Button Text'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'redirect_link',
            'text',
            [
                'name' => 'redirect_link',
                'label' => __('Redirect Link'),
                'title' => __('Redirect Link'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'show_captcha',
            'select',
            [
                'label'    => __('Show reCaptcha'),
                'title'    => __('Show reCaptcha'),
                'name'     => 'show_captcha',
                'options'  => $model->getYesno(),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'enable_tracklink',
            'select',
            [
                'label'    => __('Allow Tracking Link With QR code'),
                'title'    => __('Allow Tracking Link With QR code'),
                'note'     => __(
                    'Enabled option to support view message by QR code scan. Empty to use default module setting.'
                ),
                'name'     => 'enable_tracklink',
                'options'  => $model->getEnabledDisabled(),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'show_toplink',
            'select',
            [
                'label'    => __('Show on TopLink'),
                'title'    => __('Show on TopLink'),
                'name'     => 'show_toplink',
                'options'  => $model->getYesno(),
                'disabled' => $isElementDisabled
            ]
        );

        $groups = $this->groupRepository->getList($this->searchCriteriaBuilder->create())
            ->getItems();
        $fieldset->addField(
            'customer_group_ids',
            'multiselect',
            [
                'name'     => 'customer_group_ids[]',
                'label'    => __('Customer Groups'),
                'title'    => __('Customer Groups'),
                'required' => true,
                'disabled' => $isElementDisabled,
                'values'   =>  $this->objectConverter->toOptionArray($groups, 'id', 'code')
            ]
        );

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_id',
                'multiselect',
                [
                    'name'     => 'stores[]',
                    'label'    => __('Store View'),
                    'title'    => __('Store View'),
                    'required' => true,
                    'values'   => $this->systemStore->getStoreValuesForForm(false, true),
                    'disabled' => $isElementDisabled
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                Element::class
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                ['name' => 'stores[]', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $model->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        $fieldset->addField(
            'status',
            'select',
            [
                'label'    => __('Status'),
                'title'    => __('Status'),
                'name'     => 'status',
                'options'  => $model->getAvailableStatuses(),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'custom_template',
            'text',
            [
                'name'     => 'custom_template',
                'label'    => __('Custom Template'),
                'title'    => __('Custom Template'),
                'disabled' => $isElementDisabled,
                'note'     => __('Example: form/view.phtml, form/view2.phtml')
            ]
        );
        if (!$model->getId()) {
            $model->setData('submit_button_text', __('Click here'));
            $model->setData('status', $isElementDisabled ? '0' : '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Form Information');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Form Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden(): bool
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function isAllowedAction(string $resourceId): bool
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
