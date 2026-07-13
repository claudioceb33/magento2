<?php
namespace Swissup\SeoTemplates\Controller\Adminhtml\Template;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class NewAction extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_SeoTemplates::template_save';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Swissup_SeoTemplates::template_index')
            ->addBreadcrumb(__('Metadata Templates'), __('Metadata Templates'))
            ->addBreadcrumb(__('New Template'), __('New Template'));
        $resultPage->getConfig()->getTitle()->prepend(__('Metadata Templates'));
        $resultPage->getConfig()->getTitle()->prepend(__('New Template'));
        return $resultPage;
    }
}
