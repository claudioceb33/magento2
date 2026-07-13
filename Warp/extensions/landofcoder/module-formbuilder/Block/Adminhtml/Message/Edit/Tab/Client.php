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

namespace Lof\Formbuilder\Block\Adminhtml\Message\Edit\Tab;

use Lof\Formbuilder\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\Template\Config;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Convert\DataObject as ObjectConverter;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;

class Client extends Generic implements TabInterface
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

    /**
     * @var Data
     */
    protected Data $formHelper;
    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $templatesFactory;
    /**
     * @var Config
     */
    protected Config $emailConfig;

    /**
     * Client constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param GroupRepositoryInterface $groupRepository
     * @param ObjectConverter $objectConverter
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Store $systemStore
     * @param CollectionFactory $templatesFactory
     * @param Config $emailConfig
     * @param Data $formHelper
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
        Data $formHelper,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->groupRepository = $groupRepository;
        $this->objectConverter = $objectConverter;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->templatesFactory = $templatesFactory;
        $this->emailConfig = $emailConfig;
        $this->formHelper = $formHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('formbuilder_message');

        if ($this->isAllowedAction('Lof_Formbuilder::message')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }
        $this->_eventManager->dispatch(
            'lof_check_license',
            ['obj' => $this, 'ex' => 'Lof_Formbuilder']
        );
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('page_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Client Information')]);
        if ($model->getId()) {
            $fieldset->addField('form_id', 'hidden', ['name' => 'message_id']);
        }

        $fieldset->addField(
            'ip_address',
            'note',
            [
                'name' => 'ip_address',
                'label' => __('IP Address'),
                'title' => __('IP Address'),
                'text' => $model->getIpAddress()
            ]
        );

        $params = $model->getParams();
        $params = $this->formHelper->decodeData($params);
        $params = $this->formHelper->xssCleanArray($params);
        $objectManager = ObjectManager::getInstance();
        $escaper = $objectManager->create(\Magento\Framework\Escaper::class);
        $currentUrl = (
            isset($params['current_url']) && $params['current_url']
        ) ?
            '<a href="' . $escaper->escapeUrl($params['current_url']) .
            '" target="_BLANK">' . $escaper->escapeHtml($params['current_url']) . '</a>' : '';
        $fieldset->addField(
            'current_url',
            'note',
            [
                'name' => 'current_url',
                'label' => __('Referring Page'),
                'title' => __('Referring Page'),
                'text' => $currentUrl
            ]
        );

        $fieldset->addField(
            'host_name',
            'note',
            [
                'name' => 'host_name',
                'label' => __('Host Name'),
                'title' => __('Host Name'),
                'text' => strip_tags($params['http_host'])
            ]
        );

        $fieldset->addField(
            'brower',
            'note',
            [
                'name' => 'brower',
                'label' => __('User Agent'),
                'title' => __('User Agent'),
                'text' => strip_tags($params['brower'])
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
        return __('Client Information');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Client Information');
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
