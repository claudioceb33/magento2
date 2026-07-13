<?php

namespace Swissup\Hreflang\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Page\Config\Renderer;
use Swissup\Hreflang\Helper\Store as Helper;
use Swissup\Hreflang\Model\CurrentUrl;
use Magento\Store\Model\Store;

class AddHreflang
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var PageConfig
     */
    private $pageConfig;

    /**
     * @var CurrentUrl
     */
    private $currentUrl;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var array
     */
    private $ignoreActions = [
        'cms_noroute_index'
    ];

    /**
     * @param CurrentUrl       $currentUrl
     * @param Helper           $helper
     * @param PageConfig       $pageConfig
     * @param RequestInterface $request
     */
    public function __construct(
        CurrentUrl $currentUrl,
        Helper $helper,
        PageConfig $pageConfig,
        RequestInterface $request
    ) {
        $this->helper = $helper;
        $this->pageConfig = $pageConfig;
        $this->currentUrl = $currentUrl;
        $this->request = $request;
    }

    /**
     * Add hreflang data before rendering assets.
     *
     * @param  Renderer $subject
     * @param  array    $resultGroups
     * @return null
     */
    public function beforeRenderAssets(
        Renderer $subject,
        $resultGroups = []
    ) {
        if ($this->canUseHreflang()) {
            $store = $this->helper->getStoreManager()->getStore();
            foreach ($this->helper->getAllowedWebsites($store) as $website) {
                foreach ($website->getStores() as $store) {
                    if (!$this->helper->isExcluded($store)
                        && $url = $this->getCurrentUrlForStore($store)
                    ) {
                        $lang = $this->helper->getHreflang($store);
                        $this->addLinkHreflang($lang, $url);
                    }
                }
            }

            // add x-default when config enabled
            $xDefaultStore = $this->getXDefaultStore();
            if ($xDefaultStore
                && $url = $this->getCurrentUrlForStore($xDefaultStore)
            ) {
                $this->addLinkHreflang('x-default', $url);
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    private function canUseHreflang(): bool
    {
        $store = $this->helper->getStoreManager()->getStore();

        if (in_array($this->request->getFullActionName(), $this->ignoreActions)) {
            return false;
        }

        if ($this->helper->isEnabledInPage($store)) {
            $canonicalUrl = $this->getPageCanonicalUrl();
            if (!$canonicalUrl) {
                return true;
            }

            $currentUrl = $store->getCurrentUrl(false, ['___store']);

            return $currentUrl == $canonicalUrl;
        }

        return false;
    }

    /**
     * Get canonical url from page assets
     *
     * @return string
     */
    private function getPageCanonicalUrl(): string
    {
        $groupCanonical = $this->pageConfig
            ->getAssetCollection()
            ->getGroupByContentType('canonical');

        if ($groupCanonical) {
            $canonicals = $groupCanonical->getAll();
            $canonical = reset($canonicals);

            return $canonical ? $canonical->getUrl() : '';
        }

        return '';
    }

    /**
     * Add link to language
     *
     * @param string $language
     * @param string $href
     */
    public function addLinkHreflang(string $language, string $href)
    {
        $this->pageConfig->addRemotePageAsset(
            $href,
            'alternate',
            [
                'attributes' => [
                    'rel' => 'alternate',
                    'hreflang' => $language
                ]
            ]
        );
    }

    /**
     * Get current url in specific store (null when url not found)
     *
     * @param  Store $store
     * @return string|null
     */
    public function getCurrentUrlForStore(Store $store)
    {
        return $this->currentUrl->get($store);
    }

    /**
     * Get x-default store
     *
     * @return Store|null
     */
    public function getXDefaultStore()
    {
        $store = $this->helper->getStoreManager()->getStore();

        return $this->helper->getXDefaultStore($store);
    }
}
