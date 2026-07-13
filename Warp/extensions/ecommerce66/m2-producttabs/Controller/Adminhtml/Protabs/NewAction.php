<?php

namespace Ecommerce66\ProductTabs\Controller\Adminhtml\Protabs;

class NewAction extends \Ecommerce66\ProductTabs\Controller\Adminhtml\Protabs
{
    /**
     * Create new customer action
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        // the same form is used to create and edit
        $this->_forward('edit');
    }
}
