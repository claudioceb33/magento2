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

use Magento\Backend\Block\Widget\Grid\Container;

class Form extends Container
{
    protected function _construct()
    {
        $this->_blockGroup     = 'Lof_Formbuilder';
        $this->_controller     = 'adminhtml_form';
        $this->_headerText     = __('Forms');
        $this->_addButtonLabel = __('Add New Form');
        parent::_construct();
    }
}
