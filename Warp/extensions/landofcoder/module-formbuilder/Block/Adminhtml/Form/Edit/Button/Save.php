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

namespace Lof\Formbuilder\Block\Adminhtml\Form\Edit\Button;

use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

class Save extends Container
{
    protected $coreRegistry;

    /**
     * Save constructor.
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context);
        $this->coreRegistry = $registry;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        $this->buttonList->remove('save');
        if ($this->isAllowedAction('Lof_Formbuilder::form_save')) {
            $addButtonProps = [
                'id' => 'save',
                'label' => __('Save Form'),
                'class' => 'add',
                'button_class' => '',
                'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
                'options' => $this->getOptions(),
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ];
            $this->buttonList->add('add_new', $addButtonProps);
        }

        return parent::_prepareLayout();
    }

    /**
     * Retrieve options
     *
     * @return array
     */
    protected function getOptions()
    {
        $options = [];
        $options[] = [
            'id' => 'new-button',
            'label' => __('Save & New'),
            'data_attribute' => [
                'mage-init' => [
                    'button' => [
                        'event' => 'saveAndNew',
                        'target' => '#edit_form'
                    ]
                ]
            ]
        ];

        if ($this->coreRegistry->registry('formbuilder_form')->getId()) {
            $options[] = [
                'id' => 'duplicate-button',
                'label' => __('Save & Duplicate'),
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndDuplicate',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ];
        }

        $options[] = [
            'id' => 'close-button',
            'label' => __('Save & Close'),
            'data_attribute' => [
                'mage-init' => [
                    'button' => [
                        'event' => 'save',
                        'target' => '#edit_form'
                    ]
                ]
            ]
        ];
        return $options;
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
