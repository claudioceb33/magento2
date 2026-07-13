<?php
namespace Swissup\SeoHtmlSitemap\Block;

use \Magento\Framework\View\Element\Template\Context;
use \Magento\Catalog\Helper\Category as CategoryHelper;
use \Magento\Catalog\Model\Category;
use \Magento\Framework\View\Element\Template;
use \Swissup\SeoHtmlSitemap\Helper\Config;
use \Swissup\SeoHtmlSitemap\Model\Link as LinkModel;

class Categories extends Template implements \Magento\Framework\DataObject\IdentityInterface
{
    private $category;
    private $config;
    private $categoryHelper;
    public function __construct(
        Context $context,
        Category $category,
        Config $config,
        CategoryHelper $categoryHelper
    ) {
        $this->category = $category;
        $this->config = $config;
        $this->categoryHelper = $categoryHelper;
        parent::__construct($context);
    }

    public function getCollection()
    {
        if (!$this->config->showCategories()) {
            return false;
        }

        $parent = $this->_storeManager->getStore()->getRootCategoryId();
        $category = $this->category;

        $sortBy = $this->config->getSortBy();
        $recursionLevel = max(0, (int)$this->config->getMaxCategoriesDepth());
        $collection = $category->getCategories($parent, $recursionLevel, $sortBy, true, false);

        return $collection;
    }

    public function getItemUrl($category)
    {
        return $this->categoryHelper->getCategoryUrl($category);
    }

    public function getItemName($category)
    {
        return $category->getName();
    }

    public function getIdentities()
    {
        return [LinkModel::CACHE_TAG . '_' . 'categories'];
    }
}
