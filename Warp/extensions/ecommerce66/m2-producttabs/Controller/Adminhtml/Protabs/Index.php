<?php

namespace Ecommerce66\ProductTabs\Controller\Adminhtml\Protabs;

use Magento\Backend\App\Action;

class Index extends \Ecommerce66\ProductTabs\Controller\Adminhtml\Protabs
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Protabs'));
        $this->_view->renderLayout();
    }
}
