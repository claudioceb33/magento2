<?php

namespace Swissup\Hreflang\Model\CurrentUrl;

use Swissup\Askit\Api\Data\MessageInterface;
use Magento\Store\Api\Data\StoreInterface;

class AskitIndexIndex extends CatalogProductView
{
    /**
     * {@inheritdoc}
     */
    public function provide(
        \Magento\Store\Model\Store $store,
        $queryParamsToUnset = []
    ) {
        $pathInfo = $this->request->getAlias(
            \Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS
        );
        $rewrite = $this->getRewrite($store);
        if (!$rewrite) {
            // CMS Pages doesn't have per store view URL key
            // so there is no need to check if this is questions for CMS page
            return null;
        }

        $url = $store->getCurrentUrl(false, $queryParamsToUnset);
        $rewritePath = 'questions/' . $rewrite->getRequestPath();
        return $pathInfo == $rewritePath
            ? $url
            : str_replace($pathInfo, $rewritePath, $url);
    }

    /**
     * Get category rewrite for $store
     *
     * @param  StoreInterface $store
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite
     */
    private function getRewrite(StoreInterface $store)
    {
        $entityType = '';
        $request = $this->request;
        if ($request->getParam('item_type_id') == MessageInterface::TYPE_CATALOG_PRODUCT) {
            $entityType = 'product';
        } elseif ($request->getParam('item_type_id') == MessageInterface::TYPE_CATALOG_CATEGORY) {
            $entityType = 'category';
        }

        if (!$entityType) {
            return null;
        }

        return $this->findRewrite(
            $entityType,
            $request->getParam('id'),
            $store->getId()
        );
    }
}
