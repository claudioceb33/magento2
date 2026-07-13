<?php

namespace Swissup\SeoUrls\Helper;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class Context implements \Magento\Framework\ObjectManager\ContextInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Swissup\SeoCore\Model\Slug
     */
    protected $slug;

    /**
     * @var \Swissup\SeoUrls\Model\Layer\PredefinedFilters
     */
    protected $predefinedFilters;

    /**
     * Constructor
     *
     * @param Context $context
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CategoryRepositoryInterface $categoryRepository,
        \Swissup\SeoCore\Model\Slug $slug,
        \Swissup\SeoUrls\Model\Layer\PredefinedFilters $predefinedFilters
    ) {
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->slug = $slug;
        $this->predefinedFilters = $predefinedFilters;
    }

    /**
     * @return StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * @return CategoryRepositoryInterface
     */
    public function getCategoryRepository()
    {
        return $this->categoryRepository;
    }

    /**
     * @return \Swissup\SeoCore\Model\Slug
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return \Swissup\SeoUrls\Model\Layer\PredefinedFilters
     */
    public function getPredefinedFilters()
    {
        return $this->predefinedFilters;
    }
}
