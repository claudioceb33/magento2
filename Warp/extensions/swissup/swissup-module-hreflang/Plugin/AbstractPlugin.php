<?php

namespace Swissup\Hreflang\Plugin;

use Magento\Store\Api\Data\WebsiteInterface;

abstract class AbstractPlugin
{
    /**
     * @var \Swissup\Hreflang\Helper\Store
     */
    protected $helper;

    /**
     * Construct
     *
     * @param \Swissup\Hreflang\Helper\Store $helper [description]
     */
    public function __construct(\Swissup\Hreflang\Helper\Store $helper){
        $this->helper = $helper;
    }

    /**
     * Build URL for $pathInfo in specific $store
     *
     * @param  \Magento\Store\Model\Store $store
     * @param  string                     $pathInfo
     * @return string
     */
    protected function buildUrl(\Magento\Store\Model\Store $store, $pathInfo)
    {
        $query = [];
        $isStorecodeAsParam = !$store->isUseStoreInUrl();
        $isRemoveStorecode = $this->helper->isRemoveStorecode($store);
        if ($isStorecodeAsParam && !$isRemoveStorecode) {
            $query['___store'] = $store->getCode();
        }

        return $store->getBaseUrl()
            . $pathInfo
            . ($query ? '?' . http_build_query($query, '', '&amp;') : '');
    }

    protected function getStores(WebsiteInterface $website): array
    {
        return array_filter($website->getStores(), function ($store) {
            return !$this->helper->isExcluded($store);
        }) ?: [];
    }
}
