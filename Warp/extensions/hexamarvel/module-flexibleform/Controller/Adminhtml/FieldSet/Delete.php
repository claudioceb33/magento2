<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Controller\Adminhtml\FieldSet;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Hexamarvel\FlexibleForm\Model\FieldSetFactory
     */
    private $fieldSetFactory;

    /**
     * @var \Hexamarvel\FlexibleForm\Model\FieldFactory
     */
    private $fieldFactory;

    /**
     * @param \Magento\Backend\App\Action\Context
     * @param \Hexamarvel\FlexibleForm\Model\FieldSetFactory $fieldSetFactory
     * @param \Hexamarvel\FlexibleForm\Model\FieldFactory $fieldFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Hexamarvel\FlexibleForm\Model\FieldSetFactory $fieldSetFactory,
        \Hexamarvel\FlexibleForm\Model\FieldFactory $fieldFactory
    ) {
        parent::__construct($context);
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
        $formId = $this->getRequest()->getParam('form_id');
        if ($id) {
            try {
                $model = $this->fieldSetFactory->create();
                $model->load($id);
                $model->delete();
                $fieldCollection = $this->fieldFactory->create()->getCollection()->addFieldToFilter(
                    'fieldset',
                    $id
                );
                foreach ($fieldCollection as $key => $collection) {
                    $collection->setFieldset(0)->save();
                }

                $this->messageManager->addSuccessMessage(__('You deleted the Fieldset.'));
                return $resultRedirect->setPath('*/form/edit', ['id' => $formId]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['form_id' => $formId, 'fieldset_id' => $id]);
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find a fieldset to delete.'));
        return $resultRedirect->setPath('*/form/edit', ['id' => $formId]);
    }
}
