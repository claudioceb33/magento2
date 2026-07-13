<?php

namespace Swissup\Hreflang\Model\CategoryTree;

use Swissup\Hreflang\Helper\Store as Helper;
use Swissup\Hreflang\Model\Flag;
use Swissup\Hreflang\Model\ResourceModel\Category as ResourceCategory;

class Decorator
{
    private Flag $flag;
    private Helper $helper;
    private ResourceCategory $resourceCategory;

    public function __construct(
        Flag $flag,
        Helper $helper,
        ResourceCategory $resourceCategory
    ) {
        $this->flag = $flag;
        $this->helper = $helper;
        $this->resourceCategory = $resourceCategory;
    }

    public function decorate(array $tree): array
    {
        $isPreloadAllCategories = true;
        $this->resourceCategory->preloadStatusData([], $isPreloadAllCategories);
        foreach ($tree as &$root) {
            $this->modify($root, $root['value']);
        }

        return $tree;
    }

    private function modify(&$categoryData, $rootId): void
    {
        $stores = $this->helper->getStoreManager()->getStores();
        $category = new \Magento\Framework\DataObject([
            'id' => $categoryData['value']
        ]);
        $tail = [];
        foreach ($stores as $store) {
            if ($store->getRootCategoryId() === $rootId
                && $this->resourceCategory->isEnabled($category, $store)
            ) {
                $locale = $this->helper->getLocale($store);
                list($lang, $country) = explode('_', $locale);
                $tail[] = $this->flag->getEmoji($country);
            }
        }

        $categoryData['label'] .= ' ' . implode(' ', $tail);
        if (isset($categoryData['optgroup'])) {
            foreach ($categoryData['optgroup'] as &$childCategoryData) {
                $this->modify($childCategoryData, $rootId);
            }
        }
    }
}
