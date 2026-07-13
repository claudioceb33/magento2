<?php

namespace Swissup\SeoUrls\Helper;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Swissup\SeoUrls\Model\Config\Source\NofollowStategy;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIG_ENABLED = 'swissup_seourls/general/enabled';
    const CONFIG_FORCE_SUBCAT_URL = 'swissup_seourls/layered_navigation/category_child_url';

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Swissup\SeoUrls\Model\Layer\PredefinedFilters
     */
    protected $predefinedFiltersList;

    /**
     * @var \Swissup\SeoCore\Model\Slug
     */
    protected $slug;

    /**
     * @param Context                               $seoUrlsContext
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        Context $seoUrlsContext,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->categoryRepository = $seoUrlsContext->getCategoryRepository();
        $this->storeManager = $seoUrlsContext->getStoreManager();
        $this->predefinedFiltersList = $seoUrlsContext->getPredefinedFilters();
        $this->slug = $seoUrlsContext->getSlug();
        parent::__construct($context);
    }

    /**
     * Convert given string into seo string for url
     *
     * @param  string $string
     * @return string
     */
    public function getSeoFriendlyString($string)
    {
        $slugified = $this->slug->slugify($string);
        // workaround to replace first dash with minus sign "−" (HTML: &#8722;)
        if (strpos($slugified, '-') === 0) {
            $slugified = '−' . substr($slugified, 1);
        }

        return $slugified;
    }

    /**
     * Get predefined layer filter seo label
     *
     * @param  string $filterName
     * @return string
     */
    public function getPredefinedFilterLabel($filterName)
    {
        if ($this->predefinedFiltersList->hasData($filterName)) {
            $label = $this->predefinedFiltersList->getData($filterName)
                ->getStoreLabel();
            return $this->getSeoFriendlyString($label);
        }

        return '';
    }

    /**
     * Get predefined layer filter request var
     *
     * @param  string $filterName
     * @return string
     */
    public function getPredefinedFilterRequestVar($filterName)
    {
        if ($this->predefinedFiltersList->hasData($filterName)) {
            return $this->predefinedFiltersList->getData($filterName)
                ->getRequestVar();
        }

        return '';
    }

    /**
     * Check if SEO URLs enabled
     *
     * @return boolean
     */
    public function isSeoUrlsEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get name for search controller in URL
     *
     * @return string
     */
    public function getSearchControllerName()
    {
        $name = $this->getCurrentStore()->getConfig(
            'swissup_seourls/search/controller_name'
        );
        if (strpos($name, '.') === false) {
            // no extension
            $name = rtrim($name, '/') . '/';
        }

        return $name;
    }

    /**
     * Check config option if search term show in url body
     *
     * @return boolean
     */
    public function isSearchTermInUrl()
    {
        return (bool)$this->getCurrentStore()->getConfig(
            'swissup_seourls/search/term_place'
        );
    }

    /**
     * Get catgeory by its ID
     *
     * @param  int|string $id
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     */
    public function getCategoryById($id)
    {
        return $this->categoryRepository->get(
            $id,
            $this->storeManager->getStore()->getId()
        );
    }

    /**
     * @param  \Magento\Catalog\Model\ResourceModel\Category\Collection|\Magento\Catalog\Model\Category[] $categories
     * @return array
     */
    public function getIdsFromCategories($categories)
    {
        $ids = [];
        if (is_array($categories)) {
            foreach ($categories as $category) {
                $ids[] = $category->getId();
            }
        } else {
            $ids = $categories->getColumnValues('entity_id');
        }

        return $ids;
    }

    /**
     * Get root catgeory for current store
     *
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     */
    public function getRootCategory()
    {
        return $this->getCategoryById(
            $this->storeManager->getStore()->getRootCategoryId()
        );
    }

    /**
     * Is separate filter enabled
     *
     * @return boolean
     */
    public function isSeparateFilters()
    {
        $store = $this->getCurrentStore();
        return $this->scopeConfig->getValue(
                'swissup_seourls/layered_navigation/separate_filters',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store->getCode()
            )
            &&
            $this->scopeConfig->getValue(
                'swissup_seourls/layered_navigation/separator',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store->getCode()
            );
    }

    /**
     * Get filter separator
     *
     * @return string
     */
    public function getFiltersSeparator()
    {
        $store = $this->getCurrentStore();
        $separator = $this->scopeConfig->getValue(
            'swissup_seourls/layered_navigation/separator',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
        return trim($separator, '/');
    }

    /**
     * Get URL to homepage
     *
     * @return string
     */
    public function getHomepageUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }

    /**
     * Is homepage Redirect enabled
     *
     * @return boolean
     */
    public function isHomepageRedirect()
    {
        return $this->isSeoUrlsEnabled()
            && $this->scopeConfig->getValue(
                'swissup_seourls/cms/redirect_to_home',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }

    /**
     * Get current store
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getCurrentStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * Is category filter links with nofollow.
     *
     * @return boolean
     * @deprecated 1.5.45
     */
    public function isCategoryFilterNofollow()
    {
        return $this->isCategoryFilterNofollowForce();
    }

    public function getCategoryFilterNofollowValue(): int
    {
        return (int)$this->scopeConfig->getValue(
            'swissup_seourls/layered_navigation/category_nofolow',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isCategoryFilterNofollowForce(): bool
    {
        $value = $this->getCategoryFilterNofollowValue();

        return $value === NofollowStategy::FORCE_NOFOLLOW;
    }

    public function isCategoryFilterNofollowRemove(): bool
    {
        $value = $this->getCategoryFilterNofollowValue();

        return $value === NofollowStategy::REMOVE_NOFOLLOW;
    }

    /**
     * Is Use direct URL to subcategory.
     *
     * @return boolean
     */
    public function isForceSubcategoryUrl()
    {
        $handle = implode('_', [
            $this->_request->getRouteName(),
            $this->_request->getControllerName(),
            $this->_request->getActionName()
        ]);
        $allowedHandles = ['catalog_category_view'];

        return in_array($handle, $allowedHandles)
            && $this->scopeConfig->isSetFlag(
                self::CONFIG_FORCE_SUBCAT_URL,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }
}
