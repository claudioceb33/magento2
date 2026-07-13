<?php

namespace Swissup\Hreflang\Plugin\Sitemap\Highlight;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

class ItemProvider extends \Swissup\Hreflang\Plugin\AbstractPlugin
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Swissup\Hreflang\Helper\Sitemap
     */
    private $hreflangData;

    /**
     * @param ObjectManagerInterface           $objectManager
     * @param ScopeConfigInterface             $scopeConfig
     * @param StoreManagerInterface            $storeManager
     * @param \Swissup\Hreflang\Helper\Sitemap $hreflangData
     * @param \Swissup\Hreflang\Helper\Store   $helper
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        \Swissup\Hreflang\Helper\Sitemap $hreflangData,
        \Swissup\Hreflang\Helper\Store $helper
    ) {
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->hreflangData = $hreflangData;
        parent::__construct($helper);
    }

    public function afterGetItems(
        ItemProviderInterface $subject,
        array $result,
        $storeId
    ): array {
        $currentStore = $this->storeManager->getStore($storeId);
        if (!$this->helper->isEnabledInXmlSitemap($currentStore)) {
            return $result;
        }

        // prepare hreflang data for Highlight Page URLs
        $items = $result;
        $website = $currentStore->getWebsite();
        $xDefaultStore = $this->helper->getXDefaultStore($currentStore);
        $stores = $this->getStores($website);
        foreach ($items as $item) {
            $data = [];
            $currentUrl = $item->getUrl();
            foreach ($stores as $store) {
                if ($url = $this->getHreflangUrl($currentUrl, $store)) {
                    $lang = $this->helper->getHreflang($store);
                    $href = $this->buildUrl($store, $url);
                    $data[$lang] = $href;
                }
            }

            if ($xDefaultStore
                && $url = $this->getHreflangUrl($currentUrl, $xDefaultStore)
            ) {
                $href = $this->buildUrl($xDefaultStore, $url);
                $data['x-default'] = $href;
            }

            $this->hreflangData->addItem(
                $storeId,
                $currentUrl,
                new \Magento\Framework\DataObject(
                    [
                        'type' => 'other',
                        'collection' => $data
                    ]
                )
            );
        }

        return $result;
    }

    private function getHreflangUrl(string $urlKey, StoreInterface $store): string
    {
        $pageHelper = $this->objectManager->get('\Swissup\Highlight\Helper\Page');
        $type = $pageHelper->getPageTypeByUrlKey($urlKey);

        return (string)$this->scopeConfig->getValue(
                "highlight/{$type}/url",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            );
    }
}
