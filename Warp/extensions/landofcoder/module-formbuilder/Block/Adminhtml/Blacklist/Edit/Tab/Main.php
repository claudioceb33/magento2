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

namespace Lof\Formbuilder\Block\Adminhtml\Blacklist\Edit\Tab;

use Lof\Formbuilder\Helper\Data;
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
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;

class Main extends Generic implements TabInterface
{
    /**
     * @var Store
     */
    protected $systemStore;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ObjectConverter
     */
    protected $objectConverter;

    /**
     * @var Data
     */
    protected $formatDateFormBuilder;

    protected $templatesFactory;

    protected $emailConfig;

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
     * @param Data $formatDateFormBuilder
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
        Data $formatDateFormBuilder,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->groupRepository = $groupRepository;
        $this->objectConverter = $objectConverter;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->templatesFactory = $templatesFactory;
        $this->emailConfig = $emailConfig;
        $this->formatDateFormBuilder = $formatDateFormBuilder;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('formbuilder_blacklist');

        if ($this->isAllowedAction('Lof_Formbuilder::blacklist_edit')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }
        $this->_eventManager->dispatch(
            'lof_check_license',
            ['obj' => $this,'ex' => 'Lof_Formbuilder']
        );
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('blacklist_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Blacklist Information')]);

        $message_link = $form_link = '';
        if ($model->getId()) {
            $fieldset->addField('blacklist_id', 'hidden', ['name' => 'blacklist_id']);
            if ($model->getMessageId()) {
                $message_link = '
                <a href="' . $this->getUrl('*/message/edit/message_id/' . $model->getMessageId()) . '"
                target="_BLANK">' . __('View message') . '</a>';
            }
            if ($model->getFormId()) {
                $form_link = '<a href="' . $this->getUrl('*/form/edit/form_id/'
                        . $model->getFormId()) . '" target="_BLANK">' . __('View form profile') . '</a>';
            }
        }
        $disable_editable = $isElementDisabled;

        if ($model->getId()) {
            $disable_editable = true;
            $attr = 'readonly';
            //$emailAndIpIsUnique = '';
        } else {
            $attr = 'disabled';
        }

        $fieldset->addField(
            'ip',
            'text',
            [
                'name'     => 'ip',
                'label'    => __('IP address'),
                'title'    => __('IP address'),
                'required' => false,
                'class' => 'ip',
                $attr => $disable_editable
            ]
        );

        $fieldset->addField(
            'email',
            'text',
            [
                'name'     => 'email',
                'label'    => __('Email address'),
                'title'    => __('Email address'),
                'required' => false,
                'class' => 'email',
                $attr => $disable_editable,
            ]
        );

        $fieldset->addField(
            'form_id',
            'text',
            [
                'name' => 'form_id',
                'label' => __('Form Id'),
                'title' => __('Form Id'),
                $attr => $disable_editable
            ]
        );

        $fieldset->addField(
            'message_id',
            'text',
            [
                'name' => 'message_id',
                'label' => __('Message Id'),
                'title' => __('Message Id'),
                $attr => $disable_editable
            ]
        );

        $fieldset->addField(
            'note',
            'textarea',
            [
                'name'     => 'note',
                'label'    => __('Note'),
                'title'    => __('Note'),
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );

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

        if ($model->getId()) {
            if ($form_link) {
                $fieldset->addField(
                    'form_name',
                    'note',
                    [
                        'name' => 'form_name',
                        'label' => __('Form Profile'),
                        'title' => __('Form Profile'),
                        'text' => $form_link,
                        'class' => 'validate-email',
                    ]
                );
            }

            if ($message_link) {
                $fieldset->addField(
                    'message',
                    'note',
                    [
                        'name' => 'message',
                        'label' => __('Message'),
                        'title' => __('Message'),
                        'text' => $message_link
                    ]
                );
            }

            $fieldset->addField(
                'created_time',
                'note',
                [
                    'name' => 'created_time',
                    'label' => __('Created At'),
                    'title' => __('Created At'),
                    'text' => $this->formatDateFormBuilder->formatDateFormBuilder($model->getCreatedTime()),
                ]
            );
        }
        if (!$model->getId()) {
            $model->setData('status', $isElementDisabled ? '1' : '0');
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
        return __('Blacklist Information');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Blacklist Information');
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

    /**
     * @inheritdoc
     */
    public function _afterToHtml($html)
    {
        $script = $this->getScript();
        return parent::_afterToHtml($html) . $script;
    }

    /**
     * @return string
     */
    public function getScript(): string
    {
        return '
        <script type="text/x-magento-init">
             {
                   "*": {
                       "Lof_Formbuilder/js/LofFormbuilderValidationRule": {}
                   }
               }
        </script>
        ';
    }
}
