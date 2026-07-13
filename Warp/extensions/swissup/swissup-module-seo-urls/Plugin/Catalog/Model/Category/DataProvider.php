<?php

namespace Swissup\SeoUrls\Plugin\Catalog\Model\Category;

use Magento\Store\Model\Store;

class DataProvider
{
    /**
     * Update meta data for UI component category form
     *
     * @param  \Magento\Catalog\Model\Category\DataProvider $subject
     * @param  array                                        $result
     * @return array
     */
    public function afterGetMeta(
        \Magento\Catalog\Model\Category\DataProvider $subject,
        $result
    ) {
        $category = $subject->getCurrentCategory();
        if ($category->getStoreId() != Store::DEFAULT_STORE_ID) {
            if (!isset($result['swissup_seodata']['children'])) {
                $result['swissup_seodata']['children'] = [];
            }

            $meta = &$result['swissup_seodata']['children'];
            $meta['swissup_seourl_label']['arguments']['data']['config'] = [
                'scopeLabel' => __('[STORE VIEW]'),
                'service' => [
                    'template' => 'ui/form/element/helper/service'
                ],
                'disabled' => $category->getData('swissup_seourl_label_is_default')
            ];
        }

        return $result;
    }
}
