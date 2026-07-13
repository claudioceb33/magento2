<?php

namespace Lof\Formbuilder\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Model extends Container
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Lof_Formbuilder';
        $this->_controller = 'adminhtml_model';
        $this->_headerText = __('Models');
        $this->_addButtonLabel = __('Add New Model');
        parent::_construct();
    }
}
