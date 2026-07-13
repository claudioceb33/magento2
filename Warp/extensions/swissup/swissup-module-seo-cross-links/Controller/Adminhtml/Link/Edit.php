<?php
namespace Swissup\SeoCrossLinks\Controller\Adminhtml\Link;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Swissup\SeoCrossLinks\Model\LinkFactory;

class Edit extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_SeoCrossLinks::link_edit';

    /**
     * @var LinkFactory
     */
    protected $linkFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @param Context $context
     * @param LinkFactory $linkFactory
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        LinkFactory $linkFactory,
        PageFactory $resultPageFactory,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->linkFactory = $linkFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
    }

    /**
     * Edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('link_id');
        $link = $this->linkFactory->create();

        if ($id) {
            $link->load($id);
            if (!$link->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect  */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->registry->register('seocrosslinks_link', $link);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Swissup_SeoCrossLinks::link_index')
            ->addBreadcrumb(__('SeoCrossLinks'), __('SeoCrossLinks'))
            ->addBreadcrumb(
                $id ? $link->getTitle() : __('New Link'),
                $id ? $link->getTitle() : __('New Link')
            );

        $resultPage->getConfig()->getTitle()->prepend(__('SeoCrossLinks'));
        $resultPage->getConfig()->getTitle()->prepend(
            $id ?  "Link Title: " . $link->getTitle()  : __('New Link')
        );

        return $resultPage;
    }
}
