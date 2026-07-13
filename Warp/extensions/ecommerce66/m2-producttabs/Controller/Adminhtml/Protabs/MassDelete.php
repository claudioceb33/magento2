<?php

namespace Ecommerce66\ProductTabs\Controller\Adminhtml\Protabs;

use Magento\Backend\App\Action;

class MassDelete extends \Ecommerce66\ProductTabs\Controller\Adminhtml\Protabs
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $ids = $this->getRequest()->getPost('ids');
        if (!is_array($ids)) {
            $this->messageManager->addError(__('Please select item(s).'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            foreach ($ids as $id) {
                $this->_objectManager->create('Ecommerce66\ProductTabs\Model\Protabs')
                    ->load($id)
                    ->delete();
            }
            $this->messageManager->addSuccess(__('Total of %1 record(s) were successfully deleted.', count($ids)));

        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }
}
