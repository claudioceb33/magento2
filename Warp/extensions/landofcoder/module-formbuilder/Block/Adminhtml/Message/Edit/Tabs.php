<?php

namespace Lof\Formbuilder\Block\Adminhtml\Message\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('message_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Message Information'));
    }
}
