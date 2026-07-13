<?php
declare(strict_types=1);

namespace Ecommerce66\Brands\Controller\Adminhtml\Branddetails;

class Edit extends \Ecommerce66\Brands\Controller\Adminhtml\Branddetails
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory 
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ecommerce66_Brands::brand_details_update');
    }

    /**
     * Edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('brand_details_id');
        $model = $this->_objectManager->create(\Ecommerce66\Brands\Model\BrandDetails::class);

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Brand Details no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->_coreRegistry->register('ecommerce66_brands_brand_details', $model);

        // 3. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Brand Details') : __('New Brand Details'),
            $id ? __('Edit Brand Details') : __('New Brand Details')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Brand Detailss'));
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getId()
                ? __('Edit Brand Details %1', $model->getId())
                : __('New Brand Details')
        );
        return $resultPage;
    }
}
