<?php
namespace Swissup\SeoHtmlSitemap\Controller\Adminhtml\Link;

use \Magento\Backend\App\Action;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Registry;
use \Swissup\SeoHtmlSitemap\Model\LinkFactory;

class Delete extends Action
{
    private $linkFactory;
    /**
     * Admin resource
     */
    const ADMIN_RESOURCE = 'Swissup_SeoHtmlSitemap::link_delete';
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    public function __construct(
        Context $context,
        LinkFactory $linkFactory,
        Registry $coreRegistry
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->linkFactory = $linkFactory;
        parent::__construct($context);
    }
    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('link_id');

        if ($id) {
            try {
                $model = $this->linkFactory->create();
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('The link has been deleted.'));

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', ['link_id' => $id]);
            }
        }

        $this->messageManager->addError(__('Unable to find a link to delete.'));

        return $resultRedirect->setPath('*/*/');
    }
}
