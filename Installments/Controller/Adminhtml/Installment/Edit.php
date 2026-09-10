<?php

declare(strict_types=1);

namespace Ceb\Installments\Controller\Adminhtml\Installment;

class Edit extends \Ceb\Installments\Controller\Adminhtml\Installment
{
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Ceb\Installments\Model\InstallmentFactory $installmentFactory
     * @param \Ceb\Installments\Model\ResourceModel\Installment $installmentResource
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ceb\Installments\Model\InstallmentFactory $installmentFactory,
        \Ceb\Installments\Model\ResourceModel\Installment $installmentResource
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $coreRegistry, $installmentFactory, $installmentResource);
    }

    /**
     * Edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('installment_id');
        $model = $this->installmentFactory->create();

        // 2. Initial checking
        if ($id) {
            $this->installmentResource->load($model, $id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Installment no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->_coreRegistry->register('ceb_installments_installment', $model);

        // 3. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Installment') : __('New Installment'),
            $id ? __('Edit Installment') : __('New Installment')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Installments'));
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getId() ? __('Edit Installment %1', $model->getId()) : __('New Installment')
        );
        return $resultPage;
    }
}
