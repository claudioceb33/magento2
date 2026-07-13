<?php

namespace Swissup\Hreflang\Model\CurrentUrl;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Swissup\SeoCore\Model\CurrentUrl\ProviderInterface;

class CatalogProductView implements ProviderInterface
{
    /**
     * @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    private $rewrites = [];

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * @var [type]
     */
    protected $resource;

    /**
     * __construct
     *
     * @param \Magento\Framework\App\RequestInterface      $request
     * @param \Magento\Framework\Registry                  $registry
     * @param \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry,
        \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder,
        \Swissup\Hreflang\Model\ResourceModel\Product $resource
    ) {
        $this->request = $request;
        $this->registry = $registry;
        $this->urlFinder = $urlFinder;
        $this->resource = $resource;
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
        $product = $this->registry->registry('current_product');
        // check if product assigned to website
        if (!in_array($store->getWebsiteId(), $product->getWebsiteIds())) {
            return null;
        }

        // check if product enabled for store view
        if (!$this->resource->isEnabled($product, $store)) {
            return null;
        }

        $url = $store->getCurrentUrl(false, $queryParamsToUnset);
        $useCategories = $store->getConfig(
            \Magento\Catalog\Helper\Product::XML_PATH_PRODUCT_URL_USE_CATEGORY
        );
        $rewrite = $this->findRewrite(
            'product',
            $product->getId(),
            $store->getId(),
            $product->getCategoryId() && $useCategories ? $product->getCategoryId() : null
        );
        if (!$rewrite) {
            return $url;
        }

        return $pathInfo == $rewrite->getRequestPath()
            ? $url
            : str_replace($pathInfo, $rewrite->getRequestPath(), $url);
    }

    /**
     * Find url rewrite
     *
     * @param  string     $entityType
     * @param  int        $entityId
     * @param  int        $storeId
     * @return UrlRewrite|null
     */
    protected function findRewrite($entityType, $entityId, $storeId, $categoryId = null)
    {
        $key = $entityType . ':' . $entityId;
        if (!isset($this->rewrites[$key])) {
            $this->rewrites[$key] = $this->urlFinder->findAllByData(
                [
                    UrlRewrite::ENTITY_TYPE => $entityType,
                    UrlRewrite::ENTITY_ID => $entityId,
                    UrlRewrite::REDIRECT_TYPE => 0,
                ]
            );
        }

        foreach ($this->rewrites[$key] as $rewrite) {
            $metadata = $rewrite->getMetadata();
            if ($storeId === $rewrite->getStoreId()
                && $categoryId === ($metadata['category_id'] ?? null)
            ) {
                return $rewrite;
            }
        }

        return null;
    }
}
