<?php
namespace Swissup\SeoHtmlSitemap\Controller\Adminhtml\Link;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\Page;

class Index extends Action
{
     const ADMIN_RESOURCE = 'Swissup_SeoHtmlSitemap::link_index';

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Swissup_SeoHtmlSitemap::link_index');
        $resultPage->addBreadcrumb(__('SEO HTML Sitemap'), __('SEO HTML Sitemap'));
        $resultPage->addBreadcrumb(__('Manage Links'), __('Manage Links'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Links'));

        return $resultPage;
    }
}
