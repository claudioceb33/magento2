<?php
namespace Swissup\FieldManager\Controller\Adminhtml\Index;

class Delete extends \Swissup\FieldManager\Controller\Adminhtml\Index
{
    protected $quoteFactory;
    protected $orderFactory;
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('attribute_id');
        if ($id) {
            try {
                $model = $this->initModel();
                $model->load($id);
                if ($model->getEntityTypeId() != $this->entityTypeId || !$model->getIsUserDefined()) {
                    $this->messageManager->addError(__('You cannot delete this field.'));

                    return $resultRedirect->setPath('*/*/');
                }

                $model->delete();
                if (!$model->isObjectNew()) {
                    if (isset($this->quoteFactory)) {
                        $quote = $this->quoteFactory->create();
                        $quote->deleteAttribute($model);
                    }

                    if (isset($this->orderFactory)) {
                        $order = $this->orderFactory->create();
                        $order->deleteAttribute($model);
                    }
                }
                $this->messageManager->addSuccess(__('Field was deleted.'));

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', ['attribute_id' => $id]);
            }
        }
        $this->messageManager->addError(__('Can\'t find a field to delete.'));

        return $resultRedirect->setPath('*/*/');
    }
}
