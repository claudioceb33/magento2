<?php

namespace Swissup\Hreflang\Model\CurrentUrl;

use Magento\Framework\App\RequestInterface;
use Swissup\SeoCore\Model\CurrentUrl\ProviderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use Magento\Framework\Module\Manager as ModuleManager;

class WordpressPostView implements ProviderInterface
{
    const XML_PATH_MULTISITE_ENABLED = 'wordpress/multisite/enabled';
    const XML_PATH_MULTISITE_BLOG_ID = 'wordpress/multisite/blog_id';

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * __construct
     *
     * @param RequestInterface      $request
     * @param StoreManagerInterface $storeManager
     * @param ModuleManager         $moduleManager
     */
    public function __construct(
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        ModuleManager $moduleManager
    ) {
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->moduleManager = $moduleManager;
    }

    /**
     * {@inheritdoc}
     */
    public function provide(
        Store $store,
        $queryParamsToUnset = []
    ) {

        if (!$this->moduleManager->isEnabled('FishPig_WordPress_Multisite')) {
            return $store->getCurrentUrl(false, $queryParamsToUnset);
        }

        $currentStoreBlogId = $this->getBlogId($this->storeManager->getStore());
        $storeBlogId = $this->getBlogId($store);

        if (!$store->getConfig(self::XML_PATH_MULTISITE_ENABLED)
            || $currentStoreBlogId != $storeBlogId) {
            return null;
        }

        return $store->getCurrentUrl(false, $queryParamsToUnset);
    }

    /**
     * @param  Store  $store
     * @return string
     */
    private function getBlogId(Store $store)
    {
        return $store->getConfig(self::XML_PATH_MULTISITE_BLOG_ID);
    }
}
