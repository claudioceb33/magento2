<?php

namespace Ecommerce66\ProductTabs\Controller\Adminhtml;

abstract class Protabs extends \Magento\Backend\App\Action
{
    /**
     * Init actions
     *
     * @return $this
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Ecommerce66_ProductTabs::protabs_manage'
        )->_addBreadcrumb(
            __('Protabs'),
            __('Protabs')
        );
        return $this;
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ecommerce66_ProductTabs::protabs');
    }
}
