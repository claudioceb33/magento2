<?php
/**
 * Plugin for Magento\Cms\Controller\Page\View
 */
namespace Swissup\SeoUrls\Plugin;

use Magento\Framework\Controller\Result;
use Swissup\SeoCore\Model\Cms;
use Swissup\SeoUrls\Helper\Data;

class RedirectCmsToHomepage
{
    /**
     * @var Cms
     */
    private $cms;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Result\RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @param Cms                    $cms
     * @param Data                   $helper
     * @param Result\RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        Data $helper,
        Result\RedirectFactory $resultRedirectFactory,
        Cms $cms
    ) {
        $this->cms = $cms;
        $this->helper = $helper;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * After pluging \Magento\Cms\Controller\Page\View::execute
     *
     * @param  \Magento\Cms\Controller\Page\View $subject
     * @param  \Closure $proceed
     * @return mixed
     */
    public function afterExecute(
        \Magento\Cms\Controller\Page\View $subject,
        $result
    ) {
        if ($this->helper->isHomepageRedirect()
            && $this->cms->isHomepage()
        ) {
            return $this->getRedirect();
        }

        return $result;
    }

    /**
     * @return Result\Redirect
     */
    private function getRedirect()
    {
        $redirect = $this->resultRedirectFactory->create();
        $redirect->setHttpResponseCode(301);
        $redirect->setUrl($this->helper->getHomepageUrl());

        return $redirect;
    }
}
