<?php

namespace Lof\Formbuilder\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Modelcategory extends Container
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Lof_Formbuilder';
        $this->_controller = 'adminhtml_modelcategory';
        $this->_headerText = __('Model Categories');
        $this->_addButtonLabel = __('Add New Catgory');
        parent::_construct();
    }
}
