<?php
declare(strict_types=1);

namespace Ecommerce66\Brands\Controller\Adminhtml\Branddetails;

class Delete extends \Ecommerce66\Brands\Controller\Adminhtml\Branddetails
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ecommerce66_Brands::brand_details_delete');
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('brand_details_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create(\Ecommerce66\Brands\Model\BrandDetails::class);
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the Brand Details.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['brand_details_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a Brand Details to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
