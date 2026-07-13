<?php

namespace Swissup\SeoUrls\Model\CurrentUrl;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Session\SidResolverInterface;
use Magento\Store\Api\Data\StoreInterface;
use Swissup\SeoCore\Model\CurrentUrl\ProviderInterface;

class CatalogCategoryView extends AbstractProvider
{
    private ProviderInterface $originalCurrentUrl;
    private Registry $registry;
    private $resourceCategory;

    public function __construct(
        ObjectManagerInterface $objectManager,
        Registry $registry,
        \Swissup\SeoUrls\Model\Url\Filter $seoUrlBuilder,
        \Magento\Store\Model\App\Emulation $emulation,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->registry = $registry;

        // Use object manager to avoid direct dependency from Hreflang.
        $this->originalCurrentUrl = $objectManager->get(
            \Swissup\Hreflang\Model\CurrentUrl\CatalogCategoryView::class
        );
        $this->resourceCategory = $objectManager->get(
            \Swissup\Hreflang\Model\ResourceModel\Category::class
        );

        parent::__construct($seoUrlBuilder, $emulation, $request);
    }

    /**
     * {@inheritdoc}
     */
    public function provide(
        \Magento\Store\Model\Store $store,
        $queryParamsToUnset = []
    ) {
        if ($store->isUseStoreInUrl()) {
            // unset store param to get clean URL
            $queryParamsToUnset[] = '___store';
        }

        $seoHelper = $this->seoUrlBuilder->getData('seoHelper');
        if (!$seoHelper || !$seoHelper->isSeoUrlsEnabled()) {
            return $this->originalCurrentUrl->provide($store, $queryParamsToUnset);
        }

        // start store emulation to get correct url filters
        $this->emulation->startEnvironmentEmulation($store->getId());
        $appliedFilters = $this->getAppliedFilters();
        $this->emulation->stopEnvironmentEmulation();
        // stop store emulation

        if (empty($appliedFilters)) {
            return $this->originalCurrentUrl->provide($store, $queryParamsToUnset);
        }

        $category = $this->originalCurrentUrl->getHreflangCategory($store);
        if ($category === null) {
            return null;
        }

        $rewrite = $this->originalCurrentUrl->getCategoryUrlRewrite($store, $category);
        $url = $this->getUrlForStore($store, !!$rewrite, $queryParamsToUnset);
        if ($rewrite) {
            $pathInfo = $this->getRequestPathInfo();
            if ($pathInfo && $pathInfo != $rewrite->getRequestPath()) {
                $url = str_replace($pathInfo, $rewrite->getRequestPath(), $url);
            }

            $url = $this->seoUrlBuilder->getData('seoUrl')
                ->rebuild($url, $appliedFilters);
        }

        return $url;
    }

    /**
     * Get URL
     *
     * @param  StoreInterface $store
     * @param  boolean        $useRewrite
     * @param  array          $queryParamsToUnset
     * @return string
     */
    protected function getUrlForStore(
        StoreInterface $store,
        $useRewrite = true,
        $queryParamsToUnset = []
    ) {
        $query = [];
        foreach ($queryParamsToUnset as $param) {
            $query[$param] = null;
        }

        $isRemoveStorecode = $store->getConfig(
            \Swissup\Hreflang\Helper\Store::XML_PATH_REMOVE_STORECODE
        );
        if (!$store->isUseStoreInUrl() && !$isRemoveStorecode) {
            $query['___store'] = $store->getCode();
        }

        $sidParamName = SidResolverInterface::SESSION_ID_QUERY_PARAM;
        return $store->getUrl(
            '*/*/*',
            [
                '_current' => true,
                '_use_rewrite' => $useRewrite,
                '_nosid' => in_array($sidParamName, $queryParamsToUnset),
                '_query' => $query
            ]
        );
    }
}
