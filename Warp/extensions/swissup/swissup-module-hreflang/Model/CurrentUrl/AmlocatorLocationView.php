<?php

namespace Swissup\Hreflang\Model\CurrentUrl;

class AmlocatorLocationView  extends AmlocatorIndexIndex
{
    /**
     * @param  \Magento\Store\Model\Store $store
     * @return string|null
     */
    protected function getNewPathInfo(
        \Magento\Store\Model\Store $store
    ) {
        $route = parent::getNewPathInfo($store);
        if (!$route) {
            return null;
        }

        $location = $this->objectManager->get('\Amasty\Storelocator\Model\Location');
        if (!$location->getId() ||
            !in_array($store->getId(), $this->getStoreIds($location))
        ) {
            return null;
        }

        return rtrim($route, '/') . '/' . $location->getUrlKey();
    }

    private function getStoreIds($location): array
    {
        $storeIds = $location->getStoreIds();
        if ($storeIds !== null) {
            return $storeIds;
        }

        $stores = (string)$location->getStores();
        $stores = trim($stores, ', \n\r\t\x0B\0');

        return explode(',', $stores);
    }
}
