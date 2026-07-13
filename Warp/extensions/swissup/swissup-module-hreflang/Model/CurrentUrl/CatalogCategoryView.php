<?php

namespace Swissup\Hreflang\Model\CurrentUrl;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Store\Api\Data\StoreInterface;

class CatalogCategoryView extends CatalogProductView
{
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry,
        \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder,
        \Swissup\Hreflang\Model\ResourceModel\Category $resource
    ) {
        $this->request = $request;
        $this->registry = $registry;
        $this->urlFinder = $urlFinder;
        $this->resource = $resource;
    }

    public function getHreflangCategory(
        StoreInterface $store
    ): ?CategoryInterface {
        $category = $this->registry->registry('current_category');
        $hreflangCategory = $this->resource->getHreflangCategory($category, $store);
        if (!$hreflangCategory) {
            // Hreflang category not found.
            // Check if it is possible to use current category as hreflang category.
            $parentIds = $category->getParentIds();
            $zeroLevelCategoryId = array_shift($parentIds);
            $rootCatgeoryId = array_shift($parentIds);
            if ($rootCatgeoryId != $store->getRootCategoryId()
                || !$this->resource->isEnabled($category, $store)
            ) {
                return null;
            }

            $hreflangCategory = $category;
        }

        return $hreflangCategory;
    }

    /**
     * {@inheritdoc}
     */
    public function provide(
        \Magento\Store\Model\Store $store,
        $queryParamsToUnset = []
    ) {
        $pathInfo = (string)$this->request->getAlias(
            \Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS
        );

        $hreflangCategory = $this->getHreflangCategory($store);
        if ($hreflangCategory === null) {
            return null;
        }

        $url = $store->getCurrentUrl(false, $queryParamsToUnset);
        $rewrite = $this->getCategoryUrlRewrite($store, $hreflangCategory);
        if (!$rewrite) {
            return $url;
        }

        return $pathInfo == $rewrite->getRequestPath()
            ? $url
            : str_replace($pathInfo, $rewrite->getRequestPath(), $url);
    }

    /**
     * Get category rewrite for $store
     */
    public function getCategoryUrlRewrite(
        StoreInterface $store,
        CategoryInterface $category
    ): ?UrlRewrite {
        return $this->findRewrite(
            'category',
            $category->getId(),
            $store->getId()
        );
    }
}
