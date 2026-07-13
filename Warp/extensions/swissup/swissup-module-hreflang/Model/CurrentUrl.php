<?php

namespace Swissup\Hreflang\Model;

use Magento\Framework\Session\SidResolverInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;

class CurrentUrl
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $providerCache = [];

    /**
     * @var array
     */
    protected $queryParamsToUnset = [];

    /**
     * @var array
     */
    protected $providerFactory;

    /**
     * __construct
     *
     * @param RequestInterface       $request
     * @param StoreManagerInterface  $storeManager
     * @param ObjectManagerInterface $objectManager
     * @param array                  $providerFactory
     */
    public function __construct(
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager,
        $providerFactory = []
    ) {
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->objectManager = $objectManager;
        $this->providerFactory = $providerFactory;
        $this->queryParamsToUnset = [
            SidResolverInterface::SESSION_ID_QUERY_PARAM,
            '___from_store'
        ];
    }

    /**
     * Get current URL for specific store
     *
     * @param  \Magento\Store\Model\Store $store
     * @return string|null
     */
    public function get(\Magento\Store\Model\Store $store = null)
    {
        if (!$store
            || $store->getId() == $this->storeManager->getStore()->getId()
        ) {
            return $this->storeManager->getStore()->getCurrentUrl(
                    false,
                    $this->queryParamsToUnset
                );
        }

        $callback = [
            $this->getProvider(),
            'provide'
        ];
        if (is_callable($callback)) {
            return call_user_func($callback, $store, $this->queryParamsToUnset);
        }

        return $store->getCurrentUrl(false, $this->queryParamsToUnset);
    }

    /**
     * Get provider that can generate current URL
     *
     * @return \Swissup\SeoCore\Model\CurrentUrl\ProviderInterface|null
     */
    public function getProvider()
    {
        $actionName = $this->request->getFullActionName();
        if (!isset($this->providerFactory[$actionName])) {
            // generator factory is not specified
            return null;
        }

        if (!isset($this->providerCache[$actionName])) {
            // we save instance of current url generator
            // because no need to create new instance of it on every call
            try {
                $this->providerCache[$actionName] =
                    $this->objectManager->create($this->providerFactory[$actionName]);
            } catch (\Exception $e) {
                // failed to create object
                return null;
            }
        }

        return $this->providerCache[$actionName];
    }
}
