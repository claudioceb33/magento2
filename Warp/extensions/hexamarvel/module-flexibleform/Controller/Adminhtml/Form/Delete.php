<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Controller\Adminhtml\Form;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Hexamarvel\FlexibleForm\Model\FormFactory
     */
    private $formFactory;

    /**
     * @param \Magento\Backend\App\Action\Context
     * @param \Hexamarvel\FlexibleForm\Model\FormFactory $formFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Hexamarvel\FlexibleForm\Model\FormFactory $formFactory,
        \Hexamarvel\FlexibleForm\Model\FieldSetFactory $fieldSetFactory,
        \Hexamarvel\FlexibleForm\Model\FieldFactory $fieldFactory
    ) {
        parent::__construct($context);
        $this->formFactory = $formFactory;
        $this->fieldSetFactory = $fieldSetFactory;
        $this->fieldFactory = $fieldFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->formFactory->create();
                $model->load($id);
                $model->delete();

                $fieldSetModel = $this->fieldSetFactory->create()->getCollection()
                ->addFieldToFilter('form_id', $id);
                foreach ($fieldSetModel as $item) {
                    $item->delete();
                }

                $fieldModel = $this->fieldFactory->create()->getCollection()
                ->addFieldToFilter('form_id', $id);
                ;
                foreach ($fieldModel as $item) {
                    $item->delete();
                }

                $this->messageManager->addSuccessMessage(__('You deleted the Form.'));
                return $resultRedirect->setPath('*/*/index');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find a form to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
