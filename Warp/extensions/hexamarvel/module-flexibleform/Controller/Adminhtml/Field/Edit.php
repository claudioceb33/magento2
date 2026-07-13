<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Controller\Adminhtml\Field;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Hexamarvel\FlexibleForm\Model\FieldFactory
     */
    private $fieldFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Hexamarvel\FlexibleForm\Model\FieldFactory $fieldFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Hexamarvel\FlexibleForm\Model\FieldFactory $fieldFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->fieldFactory = $fieldFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('field_id');
        $model = $this->fieldFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Field no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/form/index');
            }
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Hexamarvel_FlexibleForm::view_form')
            ->addBreadcrumb(__('Manage Field'), __('Manage Field'));
        $resultPage->addBreadcrumb(
            $id ? __('Edit Field') : __('New Field'),
            $id ? __('Edit Field') : __('New Field')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Field'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getTitle() : __('New Field'));
        return $resultPage;
    }
}
