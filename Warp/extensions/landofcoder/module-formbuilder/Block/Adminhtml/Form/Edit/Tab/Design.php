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

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\View\Model\PageLayout\Config\BuilderInterface;
use Magento\Backend\Block\Widget\Form\Generic;

class Design extends Generic implements TabInterface
{
    /**
     * @var BuilderInterface
     */
    protected $pageLayoutBuilder;

    /**
     * Design constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param BuilderInterface $pageLayoutBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        BuilderInterface $pageLayoutBuilder,
        array $data = []
    ) {
        $this->pageLayoutBuilder = $pageLayoutBuilder;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        if ($this->isAllowedAction('Lof_Formbuilder::form_edit')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }
        $this->_eventManager->dispatch(
            'lof_check_license',
            ['obj' => $this, 'ex' => 'Lof_Formbuilder']
        );
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('form_');

        $model = $this->_coreRegistry->registry('formbuilder_form');

        $fieldset = $form->addFieldset(
            'metadesign_fieldset',
            ['legend' => __('Design'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'page_layout',
            'select',
            [
                'name' => 'page_layout',
                'label' => __('Page Layout'),
                'values' => $this->pageLayoutBuilder->getPageLayoutsConfig()->toOptionArray(),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'layout_update_xml',
            'textarea',
            [
                'name' => 'layout_update_xml',
                'style' => 'height:14em;',
                'label' => __('Custom XML'),
                'values' => $this->pageLayoutBuilder->getPageLayoutsConfig()->toOptionArray(),
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset = $form->addFieldset(
            'metacss_fieldset',
            ['legend' => __('Css'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'submit_text_color',
            'text',
            [
                'name' => 'submit_text_color',
                'label' => __('Submit Text Color'),
                'class' => __('minicolors'),
                'title' => __('Submit Text Color'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'submit_background_color',
            'text',
            [
                'name' => 'submit_background_color',
                'label' => __('Submit Background Color'),
                'class' => __('minicolors'),
                'title' => __('Submit Background Color'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'submit_hover_color',
            'text',
            [
                'name' => 'submit_hover_color',
                'label' => __('Submit Hover Color'),
                'class' => __('minicolors'),
                'title' => __('Submit Hover Color'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'input_hover_color',
            'text',
            [
                'name' => 'input_hover_color',
                'label' => __('Input Hover Color'),
                'class' => __('minicolors'),
                'title' => __('Input Hover Color'),
                'disabled' => $isElementDisabled
            ]
        );

        $this->_eventManager->dispatch('adminhtml_formbuilder_form_edit_tab_main_prepare_form', ['form' => $form]);

        if (!$model->getId()) {
            $model->setData('page_layout', '2columns-left');
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
        return __('Design');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Design');
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
