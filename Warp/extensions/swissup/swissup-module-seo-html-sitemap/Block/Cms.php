<?php
namespace Swissup\SeoHtmlSitemap\Block;

use \Magento\Framework\View\Element\Template\Context;
use \Magento\Cms\Helper\Page as CmsHelper;
use \Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use \Magento\Framework\View\Element\Template;
use \Swissup\SeoHtmlSitemap\Helper\Config;
use \Swissup\SeoHtmlSitemap\Model\Link as LinkModel;

class Cms extends Template implements \Magento\Framework\DataObject\IdentityInterface
{
    private $cmsHelper;
    private $collectionFactory;
    private $config;
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        Config $config,
        CmsHelper $cmsHelper
    ) {
        $this->cmsHelper = $cmsHelper;
        $this->collectionFactory = $collectionFactory;
        $this->config = $config;
        parent::__construct($context);
    }

    public function getCollection()
    {
        if (!$this->config->showCms()) {
            return false;
        }

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('is_active', 1);

        if ($this->config->excludeCmsPages()) {
            $excludedPages = $this->config->getExcludedCMSPages();
            $collection->addFieldToFilter('identifier', ['nin' => $excludedPages]);
        }

        $collection->addStoreFilter($this->_storeManager->getStore()->getId());
        $collection->setOrder('title', 'ASC');

        return $collection;
    }

    public function getItemUrl($page)
    {
        return $this->cmsHelper->getPageUrl($page->getPageId());
    }

    public function getItemName($page)
    {
        return $page->getTitle();
    }

    public function getIdentities()
    {
        return [LinkModel::CACHE_TAG . '_' . 'cms'];
    }
}
