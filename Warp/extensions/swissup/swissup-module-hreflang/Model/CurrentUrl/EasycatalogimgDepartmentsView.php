<?php

namespace Swissup\Hreflang\Model\CurrentUrl;

use Swissup\SeoCore\Model\CurrentUrl\ProviderInterface;

class EasycatalogimgDepartmentsView implements ProviderInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * __construct
     *
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
    }

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
        $newPathInfo = $store->getConfig(
            \Swissup\Easycatalogimg\Helper\Config::XML_PATH_DEPARTMENTS_URL
        );
        $url = $store->getCurrentUrl(false, $queryParamsToUnset);
        return $pathInfo == $newPathInfo
            ? $url
            : str_replace($pathInfo, $newPathInfo, $url);
    }
}
