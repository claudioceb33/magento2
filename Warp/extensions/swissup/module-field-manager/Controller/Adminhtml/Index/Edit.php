<?php
namespace Swissup\FieldManager\Controller\Adminhtml\Index;

class Edit extends \Swissup\FieldManager\Controller\Adminhtml\Index
{
    const ACTIVE_MENU = '';
    const TITLE = '';
    const EDIT_TITLE = '';
    const NEW_TITLE = '';

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('attribute_id');
        $model = $this->initModel()->setEntityTypeId($this->entityTypeId);

        if ($id) {
            $model->load($id);

            if (!$model->getId()) {
                $this->messageManager->addError(__('This attribute no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }

            // entity type check
            if ($model->getEntityTypeId() != $this->entityTypeId) {
                $this->messageManager->addError(__('This attribute cannot be edited.'));
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        // set entered data if was error when we do save
        $data = $this->_session->getAttributeData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $attributeData = $this->getRequest()->getParam('attribute');
        if (!empty($attributeData) && $id === null) {
            $model->addData($attributeData);
        }

        $this->coreRegistry->register('entity_attribute', $model);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(static::ACTIVE_MENU)
            ->addBreadcrumb(__(static::TITLE), __(static::TITLE))
            ->addBreadcrumb(
                $id ? __(static::EDIT_TITLE) : __(static::NEW_TITLE),
                $id ? __(static::EDIT_TITLE) : __(static::NEW_TITLE)
            );
        $resultPage->getConfig()->getTitle()->prepend(__(static::TITLE));
        $resultPage->getConfig()->getTitle()->prepend($id ? $model->getFrontendLabel() : __(static::NEW_TITLE));

        return $resultPage;
    }
}
