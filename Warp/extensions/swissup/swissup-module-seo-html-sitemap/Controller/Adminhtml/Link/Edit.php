<?php
namespace Swissup\SeoHtmlSitemap\Controller\Adminhtml\Link;

use \Magento\Backend\App\Action;
use \Magento\Framework\Registry;
use \Magento\Framework\View\Result\PageFactory;
use \Swissup\SeoHtmlSitemap\Model\LinkFactory;

class Edit extends Action
{
    /**
     * Admin resource
     */
    const ADMIN_RESOURCE = 'Swissup_SeoHtmlSitemap::link_save';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Swissup\SeoHtmlSitemap\Model\LinkFactory
     */
    protected $linkFactory;

    public function __construct(
        Action\Context $context,
        LinkFactory $linkFactory,
        PageFactory $resultPageFactory,
        Registry $registry
    ) {
        $this->coreRegistry = $registry;
        $this->linkFactory = $linkFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Swissup_SeoHtmlSitemap::link_index')
            ->addBreadcrumb(__('Site Map'), __('Site Map'));

        return $resultPage;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('link_id');
        $model = $this->linkFactory->create();

        if ($id) {
            $model->load($id);

            if (!$model->getId()) {
                $this->messageManager->addError(__('This page no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->_getSession()->getFormData(true);

        if (!empty($data)) {
            $model->setData($data);
        }

        $this->coreRegistry->register('seohtmlsitemap_link', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Link') : __('New Link'),
            $id ? __('Edit Link') : __('New Link'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Links'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getName() : __('New Link'));

        return $resultPage;
    }
}
