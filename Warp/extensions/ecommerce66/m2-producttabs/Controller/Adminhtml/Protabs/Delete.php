<?php

namespace Ecommerce66\ProductTabs\Controller\Adminhtml\Protabs;

use Magento\Backend\App\Action;

class Delete extends \Ecommerce66\ProductTabs\Controller\Adminhtml\Protabs
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->_objectManager->create('Ecommerce66\ProductTabs\Model\Protabs');
                $model->setId($id);
                $model->load($id);
                $title =  $model->getTitle();
                $model->delete();
                $this->messageManager->addSuccess(__('You deleted the item "%1".', $title));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
