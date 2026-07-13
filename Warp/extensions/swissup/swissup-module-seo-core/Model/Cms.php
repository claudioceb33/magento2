<?php

namespace Swissup\SeoCore\Model;

use Magento\Cms\Model\Page;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Cms extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var Page
     */
    protected $currentPage;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Page                 $currentPage
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Page $currentPage,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->currentPage = $currentPage;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return Page
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * @return string
     */
    private function getHomepageConfig()
    {
        return $this->scopeConfig->getValue(
            \Magento\Cms\Helper\Page::XML_PATH_HOME_PAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param  Page|null $page
     * @return boolean
     */
    public function isHomepage(Page $page = null)
    {
        if (!$page) {
            $page = $this->getCurrentPage();
        }

        $homepageId = $this->getHomepageId();
        if (!$homepageId) {
            return $this->getHomepageIdentifier() == $page->getIdentifier();
        }

        return $homepageId == $page->getId();
    }

    /**
     * @return string|null
     */
    public function getHomepageId()
    {
        $parts = explode('|', $this->getHomepageConfig(), 2);

        return $parts[1] ?? null;
    }

    /**
     * @return string|null
     */
    public function getHomepageIdentifier()
    {
        $parts = explode('|', $this->getHomepageConfig(), 2);

        return $parts[0] ?? null;
    }
}
