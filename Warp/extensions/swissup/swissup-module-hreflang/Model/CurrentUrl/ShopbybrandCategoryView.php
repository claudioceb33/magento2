<?php

namespace Swissup\Hreflang\Model\CurrentUrl;

class ShopbybrandCategoryView extends ShopbybrandIndexIndex
{
    /**
     * {@inheritdoc}
     */
    protected function getNewPathInfo(
        \Magento\Store\Model\Store $store
    ) {
        $route = parent::getNewPathInfo($store);
        if (!$route) {
            return null;
        }

        $category = $this->registry->registry('shopbybrand_category');
        if (!$category || !$this->isActive($category, $store->getId())) {
            return null;
        }

        $sufix = $this->brandHelper->getConfig('general/url_suffix');

        return $route . '/category/' . $category->getUrlKey() . $sufix;
    }
}
