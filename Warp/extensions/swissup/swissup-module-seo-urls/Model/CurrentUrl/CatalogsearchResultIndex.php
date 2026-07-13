<?php

namespace Swissup\SeoUrls\Model\CurrentUrl;

use Magento\Framework\Session\SidResolverInterface;

class CatalogsearchResultIndex extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    public function provide(
        \Magento\Store\Model\Store $store,
        $queryParamsToUnset = []
    ) {
        $seoHelper = $this->seoUrlBuilder->getData('seoHelper');
        if (!$seoHelper->isSeoUrlsEnabled()) {
            // SEO URLs disabled
            return $store->getCurrentUrl(false, $queryParamsToUnset);
        }

        $query = $this->request->getQuery();
        if (is_object($query)) {
            // save original query object
            $originalQuery = clone $query;
        }

        if ($store->isUseStoreInUrl()) {
            $query['___store'] = null;
        } else {
            $query['___store'] = $store->getCode();
        }

        foreach ($queryParamsToUnset as $p) {
            $query[$p] = null;
        }

        $sidParamName = SidResolverInterface::SESSION_ID_QUERY_PARAM;
        $params = [
            '_current' => true,
            '_use_rewrite' => true,
            '_nosid' => in_array($sidParamName, $queryParamsToUnset),
            '_query' => $query
        ];
        // start store emulation to get correct url filters
        // and search controller name
        $this->emulation->startEnvironmentEmulation($store->getId());
        $controllerName = $seoHelper->getSearchControllerName();
        $q = $seoHelper->getSeoFriendlyString($this->request->getParam('q'));
        if ($seoHelper->isSearchTermInUrl()) {
            $controllerName .= $q . '/';
            $params['_query'] = ['q' => null];
        } else {
            $params['_query'] = ['q' => $q];
        }

        $appliedFilters = $this->getAppliedFilters();
        $url = $store->getUrl('*/*/*', $params);
        $this->emulation->stopEnvironmentEmulation();
        // stop store emulation

        if (is_object($query)) {
            $this->request->setQuery($originalQuery);
        }

        $newPathInfo = $controllerName;
        if (!empty($appliedFilters)) {
            $newPathInfo .= implode('/', $appliedFilters) . '/';
        }

        $requestPathInfo = $this->getRequestPathInfo();
        if (!$requestPathInfo) {
            $requestPathInfo = 'catalogsearch/result/index/';
        }

        $url = str_replace($requestPathInfo, $newPathInfo, $url);
        return $url;
    }
}
