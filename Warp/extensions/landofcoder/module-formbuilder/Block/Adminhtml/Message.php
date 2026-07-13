<?php

namespace Lof\Formbuilder\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Message extends Container
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Lof_Formbuilder';
        $this->_controller = 'adminhtml_message';
        $this->_headerText = __('Messages');
        parent::_construct();
    }
}
