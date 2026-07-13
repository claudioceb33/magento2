<?php
namespace Swissup\FieldManager\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Swissup\FieldManager\Helper\BackendGrid;

class Index extends \Magento\Backend\App\Action
{
    const TITLE = '';
    const KEY_WEBSITE = '';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var BackendGrid
     */
    private $gridHelper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param BackendGrid $gridHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        BackendGrid $gridHelper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->gridHelper = $gridHelper;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $this->gridHelper->setCurrentWebsiteId(
            static::KEY_WEBSITE,
            $this->getRequest()->getParam('website')
        );

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(static::ADMIN_RESOURCE);
        $resultPage->addBreadcrumb(__(static::TITLE), __(static::TITLE));
        $resultPage->getConfig()->getTitle()->prepend(__(static::TITLE));

        return $resultPage;
    }
}
