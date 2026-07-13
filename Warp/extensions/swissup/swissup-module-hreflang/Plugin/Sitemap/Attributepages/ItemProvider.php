<?php

namespace Swissup\Hreflang\Plugin\Sitemap\Attributepages;

use Magento\Framework\DataObject;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

class ItemProvider extends \Swissup\Hreflang\Plugin\AbstractPlugin
{

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Swissup\Hreflang\Helper\Sitemap
     */
    private $hreflangData;

    /**
     * @var array
     */
    private $pagesData;

    /**
     * @param StoreManagerInterface            $storeManager
     * @param \Swissup\Hreflang\Helper\Sitemap $hreflangData
     * @param \Swissup\Hreflang\Helper\Store   $helper
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        \Swissup\Hreflang\Helper\Sitemap $hreflangData,
        \Swissup\Hreflang\Helper\Store $helper
    ) {
        $this->storeManager = $storeManager;
        $this->hreflangData = $hreflangData;
        parent::__construct($helper);
    }

    public function afterGetCollection(
        ItemProviderInterface $subject,
        AbstractCollection $result,
        $storeId
    ) {
        $currentStore = $this->storeManager->getStore($storeId);
        if (!$this->helper->isEnabledInXmlSitemap($currentStore)) {
            return $result;
        }

        $collection = $result;
        $this->initializePagesData($collection, $storeId);

        $website = $currentStore->getWebsite();
        $xDefaultStore = $this->helper->getXDefaultStore($currentStore);
        $stores = $this->getStores($website);

        foreach ($collection as $page) {
            $data = [];
            $currentUrl = $this->getUrlKey($page, $storeId);

            foreach ($stores as $store) {
                if ($url = $this->getHreflangUrl($page, $store)) {
                    $lang = $this->helper->getHreflang($store);
                    $href = $this->buildUrl($store, $url);
                    $data[$lang] = $href;
                }
            }

            if ($xDefaultStore
                && $url = $this->getHreflangUrl($page, $xDefaultStore)
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

    private function getUrlKey(DataObject $page, $storeId): string
    {
        $url = $page->getIdentifier();

        if ($parent = $page->getParentPage($storeId)) {
            $url = $parent->getIdentifier() . '/' . $url;
        } elseif ($page->getOptionId()) { // is options based page
            return '';
        }

        return rtrim($url, '/');
    }

    private function initializePagesData(AbstractCollection $collection, $storeId)
    {
        $attributeIds = $collection->getColumnValues('attribute_id');
        $attributeIds = array_unique($attributeIds);

        $connection = $collection->getConnection();
        $select = $connection->select()
            ->from(
                ['main_table' => $collection->getMainTable()],
                ['attribute_id', 'option_id', 'identifier']
            )->join(
                ['store_table' => $collection->getTable('swissup_attributepages_store')],
                'main_table.entity_id = store_table.entity_id',
                ['store_id']
            )->where(
                'use_for_attribute_page = ?',
                1
            )->where(
                'store_id <> ?', $storeId
            )->where(
                'attribute_id IN (?)', $attributeIds
            );

        $this->pagesData = $connection->fetchAll($select);

        return $this;
    }

    private function getHreflangUrl(DataObject $currentPage, StoreInterface $store): string
    {
        $allowedStores = [
            $store->getId(),
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        ];

        $items = array_filter(
            $this->pagesData,
            function ($data) use ($currentPage, $allowedStores) {
                return $data['attribute_id'] == $currentPage->getAttributeId() &&
                    $data['option_id'] == $currentPage->getOptionId() &&
                    in_array($data['store_id'], $allowedStores);
            }
        );

        if (!$item = reset($items)) {
            return '';
        }

        $page = new DataObject($item);
        $this->assignParentPage($page, $store->getId());

        return $this->getUrlKey($page, $store->getId());
    }

    private function assignParentPage(DataObject $page, $storeId)
    {
        $optionId = $page->getOptionId();
        if (!$optionId) {
            return;
        }

        $allowedStores = [
            $storeId,
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        ];
        $parents = array_filter(
            $this->pagesData,
            function ($data) use ($page, $allowedStores) {
                return $data['attribute_id'] == $page->getAttributeId() &&
                    is_null($data['option_id']) &&
                    in_array($data['store_id'], $allowedStores);
            }
        );
        $parent = reset($parents);

        if (is_array($parent)) {
            $page->setParentPage(
                new DataObject([
                    $storeId => new DataObject($parent)
                ])
            );
        }
    }
}
