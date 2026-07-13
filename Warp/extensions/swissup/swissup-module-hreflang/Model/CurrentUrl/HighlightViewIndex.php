<?php

namespace Swissup\Hreflang\Model\CurrentUrl;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Swissup\SeoCore\Model\CurrentUrl\ProviderInterface;

class HighlightViewIndex implements ProviderInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\RequestInterface   $request
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param ScopeConfigInterface                      $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->request = $request;
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
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
        $pageHelper = $this->objectManager->get('\Swissup\Highlight\Helper\Page');
        $type = $pageHelper->getPageTypeByUrlKey($pathInfo);
        $newPathInfo = $this->scopeConfig->getValue(
            "highlight/{$type}/url",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        if (!$newPathInfo) {
            return null;
        }

        $url = $store->getCurrentUrl(false, $queryParamsToUnset);
        return $pathInfo == $newPathInfo
            ? $url
            : str_replace($pathInfo, $newPathInfo, $url);
    }
}
