<?php

namespace Swissup\Hreflang\Model\CurrentUrl;

class ShopbybrandBrandView extends ShopbybrandIndexIndex
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

        $brand = $this->registry->registry('current_brand');
        if (!$brand || !$this->isActive($brand, $store->getId())) {
            return null;
        }

        $sufix = $this->brandHelper->getConfig('general/url_suffix');

        return $route . '/' . $brand->getUrlKey() . $sufix;
    }
}
