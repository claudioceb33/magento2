<?php
declare(strict_types=1);

namespace Ceb\Installments\Controller\Adminhtml\Installment;

class Delete extends \Ceb\Installments\Controller\Adminhtml\Installment
{

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
        $id = $this->getRequest()->getParam('installment_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create(\Ceb\Installments\Model\Installment::class);
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the Installment.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['installment_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a Installment to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}

