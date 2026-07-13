<?php

namespace Swissup\SeoCore\Model\CurrentUrl;

/**
 * Current URL Provide Interface
 */
interface ProviderInterface
{
    /**
     * Provide current url representation for $store
     *
     * @param  \Magento\Store\Model\Store $store
     * @param  array                      $queryParamsToUnset
     * @return string|null
     */
    public function provide(\Magento\Store\Model\Store $store, $queryParamsToUnset = []);
}
