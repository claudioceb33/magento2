<?php

namespace Swissup\Hreflang\Model\Config\Source\CmsIdentifier;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Store\Model\Store;
use Swissup\Hreflang\Model\Flag;
use Swissup\Hreflang\Helper\Store as Helper;

class LabelBuilder
{
    private $helper;
    /**
     * @var Flag
     */
    private $flag;

    public function __construct(
        Flag $flag,
        Helper $helper
    ) {
        $this->flag = $flag;
        $this->helper = $helper;
    }

    public function build(
        PageInterface $page
    ): string {
        $storeManager = $this->helper->getStoreManager();

        $parts = [];
        foreach ($page->getStores() as $storeId) {
            $store = $storeManager->getStore($storeId);
            $locale = $this->helper->getLocale($store);
            list($lang, $country) = explode('_', $locale);

            $parts[] = $this->flag->getEmoji($country) . ' ' . $this->getStoreName($store);
        }

        return $page->getIdentifier() . '|' . implode('|', $parts);
    }

    private function getStoreName(
        Store $store
    ): string {
        $id = $store->getId();
        $name = $store->getName();

        return Store::DEFAULT_STORE_ID == $id ? __('All Store Views') : $name;
    }
}
