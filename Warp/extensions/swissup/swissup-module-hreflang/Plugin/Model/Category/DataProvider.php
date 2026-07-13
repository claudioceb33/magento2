<?php

namespace Swissup\Hreflang\Plugin\Model\Category;

use Magento\Catalog\Model\Category\DataProvider as Subject;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class DataProvider
{
    private StoreManagerInterface $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * Update meta data for UI component category form
     */
    public function afterGetMeta(
        Subject $subject,
        array $result
    ): array {
        $category = $subject->getCurrentCategory();
        if (!isset($result['swissup_seodata']['children'])) {
            $result['swissup_seodata']['children'] = [];
        }

        $meta = &$result['swissup_seodata']['children'];
        $meta['swissup_hreflang_data']['arguments']['data']['config'] = [
            'visible' => (
                !$this->storeManager->isSingleStoreMode()
                && $category->getStoreId() == Store::DEFAULT_STORE_ID
            )
        ];

        return $result;
    }
}
