<?php
namespace Swissup\SeoHtmlSitemap\Controller\Index;

use \Magento\Framework\App\Action\Action;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;
use \Swissup\SeoHtmlSitemap\Helper\Config;

class Index extends Action
{
    /**
     * Get extension configuration helper
     * @var \Swissup\SeoHtmlSitemap\Helper\Config
     */
    protected $configHelper;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Config $configHelper
    ) {
        $this->configHelper = $configHelper;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $pageConfig = $resultPage->getConfig();

        $title = $this->configHelper->getPageTitle();
        $pageConfig->getTitle()->set($title);

        $description = $this->configHelper->getMetaDescription();
        $pageConfig->setDescription($description);

        $keywords = $this->configHelper->getMetaKeywords();
        $pageConfig->setKeywords($keywords);

        return $resultPage;
    }
}
