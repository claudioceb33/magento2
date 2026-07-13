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

use Magento\Framework\View\Element\Template;

class License extends Template
{
    protected function _toHtml(): string
    {
        $this->_eventManager->dispatch('lof_check_license', ['obj' => $this,'ex' => 'Lof_Formbuilder']);
        if (!$this->getData('is_valid')) {
            return '<div id="messages">
<div class="messages"><div class="message message-error error">
<div data-ui-id="messages-message-error">
Your licence is assigned to the different store domain. Please, login to your account in
<a href="http://landofcoder.com">landofcoder.com</a> and check your licence status . </div></div></div></div>';
        }
        return parent::_toHtml();
    }
}
