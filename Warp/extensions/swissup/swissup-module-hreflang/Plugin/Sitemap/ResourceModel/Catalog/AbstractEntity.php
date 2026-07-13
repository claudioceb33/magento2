<?php

namespace Swissup\Hreflang\Plugin\Sitemap\ResourceModel\Catalog;

use Magento\Store\Model\Store;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

abstract class AbstractEntity extends \Swissup\Hreflang\Plugin\AbstractPlugin
{
    /**
     * @var string
     */
    protected $entityType;

    /**
     * @var \Swissup\Hreflang\Helper\Sitemap
     */
    protected $hreflangData;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * @var null|string|bool|int|Store
     */
    private $storeId;

    /**
     * @param \Swissup\Hreflang\Helper\Sitemap             $hreflangData
     * @param \Magento\Store\Model\StoreManagerInterface   $storeManager
     * @param \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder
     * @param \Swissup\Hreflang\Helper\Store               $helper
     */
    public function __construct(
        \Swissup\Hreflang\Helper\Sitemap $hreflangData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder,
        \Swissup\Hreflang\Helper\Store $helper
    ) {
        $this->hreflangData = $hreflangData;
        $this->storeManager = $storeManager;
        $this->urlFinder = $urlFinder;
        parent::__construct($helper);
    }

    /**
     * Save call arguments for after plugin. Compatibility with Magento 2.1.x.
     *
     * @param  AbstractDb                 $subject
     * @param  null|string|bool|int|Store $storeId
     */
    public function beforeGetCollection(
        AbstractDb $subject,
        $storeId
    ) {
        $this->storeId = $storeId; // Compatibility with Magento 2.1.x.

        return null;
    }

    /**
     * After method getCollection.
     *
     * Collect hreflang data for {$this->entityType}
     *
     * @param  AbstractDb                 $subject
     * @param  array                      $result
     * @param  null|string|bool|int|Store $storeId
     * @return array
     */
    public function afterGetCollection(
        AbstractDb $subject,
        $result,
        $storeId = null
    ) {
        // Compatibility with Magento 2.1.x.
        $storeId = $storeId ?: $this->storeId;

        $currentStore = $this->storeManager->getStore($storeId);
        if (!$this->helper->isEnabledInXmlSitemap($currentStore)) {
            return $result;
        }

        // prepare hreflang data for {$this->entityType} URLs
        $this->prepareData($result);
        $websites = $this->helper->getAllowedWebsites($currentStore);
        $xDefaultStore = $this->helper->getXDefaultStore($currentStore);
        foreach ($result as $item) {
            $data = [];
            foreach ($websites as $website) {
                foreach ($website->getStores() as $store) {
                    if ($this->helper->isExcluded($store)) {
                        continue;
                    }

                    if ($this->isItemEnabled($item, $store)) {
                        $lang = $this->helper->getHreflang($store);
                        $href = $this->getHref($store, $item->getId());
                        $data[$lang] = $href;
                    }

                }

                if ($xDefaultStore
                    && $this->isItemEnabled($item, $xDefaultStore)
                ) {
                    $href = $this->getHref($xDefaultStore, $item->getId());
                    $data['x-default'] = $href;
                }

                $this->hreflangData->addItem(
                    $storeId,
                    $item->getUrl(),
                    new \Magento\Framework\DataObject(
                        [
                            'type' => $this->entityType,
                            'collection' => $data
                        ]
                    )
                );
            }
        }

        return $result;
    }

    /**
     * Get href (URL) for {$this->entityType} with ID $itemId in $store
     *
     * @param  \Magento\Store\Model\Store $store
     * @param  int                        $itemId
     * @return string
     */
    protected function getHref(\Magento\Store\Model\Store $store, $itemId)
    {
        $rewrite = $this->findRewrite($itemId, $store->getId());

        if ($rewrite) {
            $pathInfo = $rewrite->getRequestPath();
        } else {
            $pathInfo = "catalog/{$this->entityType}/view/id/{$itemId}";
        }

        return $this->buildUrl($store, $pathInfo);
    }

    /**
     * Find url rewrite
     *
     * @param  int        $entityId
     * @param  int        $storeId
     * @return UrlRewrite
     */
    protected function findRewrite($entityId, $storeId)
    {
        return $this->urlFinder->findOneByData(
                [
                    UrlRewrite::ENTITY_TYPE => $this->entityType,
                    UrlRewrite::ENTITY_ID => $entityId,
                    UrlRewrite::STORE_ID => $storeId,
                    UrlRewrite::REDIRECT_TYPE => 0,
                ]
            );
    }

    /**
     * @param  array  $items
     * @return $this
     */
    protected function prepareData(array $items)
    {
        return $this;
    }

    /**
     * @param  \Magento\Framework\DataObject $item
     * @param  \Magento\Store\Model\Store    $store
     * @return boolean
     */
    public function isItemEnabled(
        \Magento\Framework\DataObject $item,
        \Magento\Store\Model\Store $store
    ): bool {
        return true;
    }
}
