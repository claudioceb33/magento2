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

namespace Lof\Formbuilder\Block\Adminhtml;

use Magento\Backend\Block\Widget\Container;

class FormMenu extends Container
{
    /**
     * @return FormMenu
     */
    protected function _prepareLayout()
    {
        $this->buttonList->remove('save');
        $addButtonProps = [
            'id' => 'save',
            'label' => __('Save'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options' => $this->getEventTypes()
        ];
        $this->buttonList->add('add_new', $addButtonProps);

        return parent::_prepareLayout();
    }

    /**
     * Retrieve options for 'Add Email' split button
     *
     * @return array
     */
    protected function getEventTypes(): array
    {
        return [];
    }

    /**
     * Retrieve email create url by specified email type
     *
     * @param string $type
     * @return string
     */
    protected function getEmailCreateUrl(string $type): string
    {
        return $this->getUrl(
            'loffollowupemail/*/new',
            ['event_type' => $type]
        );
    }

    /**
     * Check whether it is single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode(): bool
    {
        return $this->_storeManager->isSingleStoreMode();
    }
}
