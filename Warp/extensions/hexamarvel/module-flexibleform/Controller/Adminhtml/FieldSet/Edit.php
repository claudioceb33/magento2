<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Controller\Adminhtml\FieldSet;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Hexamarvel\FlexibleForm\Model\FieldSetFactory
     */
    private $fieldSetFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Hexamarvel\FlexibleForm\Model\FieldSetFactory $fieldSetFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Hexamarvel\FlexibleForm\Model\FieldSetFactory $fieldSetFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->fieldSetFactory = $fieldSetFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('fieldset_id');
        $model = $this->fieldSetFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Fieldset no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $formId = $this->getRequest()->getParam('form_id');
                return $resultRedirect->setPath('*/form/edit/'.$formId);
            }
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Hexamarvel_FlexibleForm::view_form')
            ->addBreadcrumb(__('Manage Fieldsets'), __('Manage Fieldset'));
        $resultPage->addBreadcrumb(
            $id ? __('Edit Fieldset') : __('New Fieldset'),
            $id ? __('Edit Fieldset') : __('New Fieldset')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Fieldset'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getTitle() : __('New Fieldset'));
        return $resultPage;
    }
}
