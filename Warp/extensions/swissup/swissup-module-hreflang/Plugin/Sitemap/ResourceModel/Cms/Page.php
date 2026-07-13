<?php
/**
 * Plugins for methods in \Magento\Sitemap\Model\ResourceModel\Cms\Page
 */
namespace Swissup\Hreflang\Plugin\Sitemap\ResourceModel\Cms;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Page extends \Swissup\Hreflang\Plugin\AbstractPlugin
{
    /**
     * @var \Swissup\Hreflang\Helper\Sitemap
     */
    protected $hreflangData;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var null|string|bool|int|Store
     */
    private $storeId;

    /**
     * @param \Swissup\Hreflang\Helper\Store             $helper
     * @param \Swissup\Hreflang\Helper\Sitemap           $hreflangData
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Swissup\Hreflang\Helper\Sitemap $hreflangData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Swissup\Hreflang\Helper\Store $helper
    ) {
        $this->hreflangData = $hreflangData;
        $this->storeManager = $storeManager;
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
     * Collect hreflang data for CMS Pages
     *
     * @param  AbstractDb $subject
     * @param  callable   $proceed
     * @param  string     $storeId
     * @return array
     */
    public function afterGetCollection(
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $subject,
        $result,
        $storeId = null
    ) {
        // Compatibility with Magento 2.1.x.
        $storeId = $storeId ?: $this->storeId;

        $currentStore = $this->storeManager->getStore($storeId);
        if (!$this->helper->isEnabledInXmlSitemap($currentStore)) {
            return $result;
        }

        // prepare hreflang data for CMS Page URLs
        $website = $currentStore->getWebsite();
        $xDefaultStore = $this->helper->getXDefaultStore($currentStore);
        foreach ($result as $item) {
            $data = [];
            foreach ($website->getStores() as $store) {
                if ($this->helper->isExcluded($store)) {
                    continue;
                }

                if ($identifier = $this->hreflangData->getHreflangIdentifier($item, $store)) {
                    $lang = $this->helper->getHreflang($store);
                    $href = $this->buildUrl($store, $identifier);
                    $data[$lang] = $href;
                }
            }

            if ($xDefaultStore
                && $identifier = $this->hreflangData->getHreflangIdentifier($item, $xDefaultStore)
            ) {
                $href = $this->buildUrl($xDefaultStore, $identifier);
                $data['x-default'] = $href;
            }

            $this->hreflangData->addItem(
                $storeId,
                $item->getUrl(),
                new \Magento\Framework\DataObject(
                    [
                        'type' => 'cms',
                        'collection' => $data
                    ]
                )
            );
        }

        return $result;
    }
}
