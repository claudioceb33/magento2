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

namespace Lof\Formbuilder\Block\Adminhtml\Form\Edit;

use Exception;
use Lof\Formbuilder\Block\Adminhtml\Form\Edit\Tab\Design;
use Lof\Formbuilder\Block\Adminhtml\Form\Edit\Tab\Main;
use Magento\Framework\Exception\LocalizedException;
use Lof\Formbuilder\Block\Adminhtml\Form\Edit\Tab\Content;
use Lof\Formbuilder\Block\Adminhtml\Form\Edit\Tab\Creator;
use Lof\Formbuilder\Block\Adminhtml\Form\Edit\Tab\Meta;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @throws LocalizedException
     * @throws Exception
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('form_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Form Information'));

        $this->addTab(
            'main_section',
            [
                'label' => __('Form Information'),
                'content' => $this->getLayout()->createBlock(Main::class)->toHtml()
            ]
        );

        $this->addTab(
            'content_section',
            [
                'label' => __('Form Content'),
                'content' => $this->getLayout()->createBlock(Content::class)->toHtml()
            ]
        );

        $this->addTab(
            'creator_section',
            [
                'label' => __('Form Creator'),
                'content' => $this->getLayout()->createBlock(Creator::class)->toHtml()
            ]
        );

        $this->addTab(
            'meta_section',
            [
                'label' => __('SEO'),
                'content' => $this->getLayout()->createBlock(Meta::class)->toHtml()
            ]
        );

        $this->addTab(
            'design_section',
            [
                'label' => __('Design'),
                'content' => $this->getLayout()->createBlock(Design::class)->toHtml()
            ]
        );

        $this->addTab(
            'messages',
            [
                'label' => __('Messages'),
                'url' => $this->getUrl('formbuilder/*/messages', ['_current' => true]),
                'class' => 'ajax'
            ]
        );
    }
}
