<?php

namespace Swissup\SeoCanonical\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Swissup\SeoCore\Model\RegistryLocator;
use Swissup\SeoCore\Model\Cms;

class Data extends Config
{
    /**
     * @var \Swissup\SeoCanonical\Model\RegistryLocator
     */
    protected $locator;

    /**
     * @var \Swissup\SeoCanonical\Model\ParentProduct
     */
    protected $parentProduct;

    /**
     * @var \Swissup\SeoCanonical\Model\UrlMaker
     */
    protected $urlMaker;

    /**
     * @var Cms
     */
    protected $cms;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Constructor
     *
     * @param RegistryLocator                           $registryLocator
     * @param \Swissup\SeoCanonical\Model\ParentProduct $parentProduct
     * @param \Swissup\SeoCanonical\Model\UrlMaker      $urlMaker
     * @param Cms                                       $cms
     * @param StoreManagerInterface                     $storeManager
     * @param Context                                   $context
     */
    public function __construct(
        RegistryLocator $registryLocator,
        \Swissup\SeoCanonical\Model\ParentProduct $parentProduct,
        \Swissup\SeoCanonical\Model\UrlMaker $urlMaker,
        Cms $cms,
        StoreManagerInterface $storeManager,
        Context $context
    ) {
        $this->locator = $registryLocator;
        $this->parentProduct = $parentProduct;
        $this->urlMaker = $urlMaker;
        $this->cms = $cms;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Get current category canonical URL
     *
     * @return string
     */
    public function getCatalogCategoryCanonicalUrl($ignoreRoot = true)
    {
        $category = $this->locator->getCategory();
        if ($ignoreRoot && $category && $this->isRoot($category)) {
            return '';
        }

        if (!$category) {
            return '';
        }

        $forcedStoreId = $this->getCategoryForcedStoreId($category->getStoreId());
        $customBaseUrl = $this->getCategoryCustomBaseUrl();
        $url = $category ?
            $this->urlMaker->getCategoryUrl($category, $forcedStoreId) :
            '';

        return $this->replaceBaseUrl($url, $forcedStoreId, $customBaseUrl);
    }

    /**
     * Get current product canonical URL
     *
     * @return string
     */
    public function getCatalogProductCanonicalUrl()
    {
        $product = $this->locator->getProduct();
        if ($product && $this->canProductUseParent()) {
            $parent = $this->parentProduct->get($product);
            $product = $parent ?: $product;
        }

        if (!$product) {
            return '';
        }

        $forcedStoreId = $this->getProductForcedStoreId($product->getStoreId());
        $customBaseUrl = $this->getProductCustomBaseUrl();
        $url = $product ?
            $this->urlMaker->getProductUrl($product, $forcedStoreId) :
            '';

        return $this->replaceBaseUrl($url, $forcedStoreId, $customBaseUrl);
            '';
    }

    /**
     * Get current CMS page canonical URL
     *
     * @return string
     */
    public function getCmsPageCanonicalUrl()
    {
        $currentPage = $this->cms->getCurrentPage();
        if ($currentPage && $currentPage->getId()) {
            if ($this->cms->isHomepage()) {
                $store = $this->storeManager->getStore();

                return $store->getBaseUrl();
            }

            return $this->urlMaker->getCmsPageUrl($currentPage);
        }

        return '';
    }

    /**
     * Check if category is root
     *
     * @param  \Magento\Catalog\Api\Data\CategoryInterface $category
     * @return boolean
     */
    private function isRoot($category)
    {
        $store = $this->storeManager->getStore($category->getStoreId());
        return $category->getId() == $store->getRootCategoryId();
    }

    /**
     * @param  string $subjectUrl
     * @return string
     */
    private function replaceBaseUrl($subjectUrl, $storeId, $customBaseUrl) {
        $store = $this->storeManager->getStore($storeId ?: null);

        return $customBaseUrl ?
            str_replace($store->getBaseUrl(), $customBaseUrl, $subjectUrl) :
            $subjectUrl;
    }
}
