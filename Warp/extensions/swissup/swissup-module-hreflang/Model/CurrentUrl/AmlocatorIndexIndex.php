<?php

namespace Swissup\Hreflang\Model\CurrentUrl;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Swissup\SeoCore\Model\CurrentUrl\ProviderInterface;

class AmlocatorIndexIndex  implements ProviderInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Amasty\Storelocator\Model\ConfigProvider
     */
    protected $configProvider;

    /**
     * __construct
     *
     * @param RequestInterface       $request
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        RequestInterface $request,
        ObjectManagerInterface $objectManager
    ) {
        $this->request = $request;
        $this->objectManager = $objectManager;
        $this->configProvider = $objectManager->get('\Amasty\Storelocator\Model\ConfigProvider');
    }

    /**
     * {@inheritdoc}
     */
    public function provide(
        \Magento\Store\Model\Store $store,
        $queryParamsToUnset = []
    ) {
        $pathInfo = trim($this->request->getPathInfo(), '/');
        if (!$newPathInfo = $this->getNewPathInfo($store)) {
            return null;
        }

        $url = $store->getCurrentUrl(false, $queryParamsToUnset);

        return $pathInfo == $newPathInfo
            ? $url
            : str_replace('/' . $pathInfo, '/' . $newPathInfo, $url);
    }

    /**
     * @param  \Magento\Store\Model\Store $store
     * @return string|null
     */
    protected function getNewPathInfo(
        \Magento\Store\Model\Store $store
    ) {
        return $this->configProvider->getUrl($store->getCode());
    }
}
