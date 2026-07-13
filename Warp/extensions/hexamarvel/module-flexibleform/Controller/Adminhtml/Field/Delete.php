<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Controller\Adminhtml\Field;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Hexamarvel\FlexibleForm\Model\FieldSetFactory
     */
    private $fieldFactory;

    /**
     * @param \Magento\Backend\App\Action\Context
     * @param \Hexamarvel\FlexibleForm\Model\FieldFactory $fieldFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Hexamarvel\FlexibleForm\Model\FieldFactory $fieldFactory
    ) {
        parent::__construct($context);
        $this->fieldFactory = $fieldFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        $formId = $this->getRequest()->getParam('form_id');
        if ($id) {
            try {
                $model = $this->fieldFactory->create();
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the Field.'));
                return $resultRedirect->setPath('*/form/edit', ['id' => $formId]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['form_id' => $formId, 'field_id' => $id]);
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find a field to delete.'));
        return $resultRedirect->setPath('*/form/edit', ['id' => $formId]);
    }
}
