<?php

namespace Swissup\Hreflang\Model\ResourceModel;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category as ResourceCategory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Category extends EntityEav
{
    const DATA_KEY_LINKS = 'hreflang_links';
    const DATA_KEY_CATEGORIES = 'hreflang_categories';
    const MAIN_TABLE = 'swissup_hreflang_category';

    private CategoryListInterface $categoryList;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    public function __construct(
        CategoryListInterface $categoryList,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ResourceCategory $resource,
        StoreManagerInterface $storeManager
    ) {
        $this->categoryList = $categoryList;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;

        parent::__construct($resource, $storeManager, 'is_active');
    }

    public function getMainTable()
    {
        return $this->resource->getTable(self::MAIN_TABLE);
    }

    public function getConnection()
    {
        return $this->resource->getConnection();
    }

    public function isEnabled(DataObject $category, Store $store): bool
    {
        $attribute = $this->getStatusAttribute();
        $id = $category->getId();
        $data = $this->statusData[$id] ??
            ($this->preloadStatusData([$category])->statusData[$id] ?? []);
        $allStoreviewsId = $this->getAllStoreviewsId();
        $status = $data[(int)$store->getId()] ?? ($data[$allStoreviewsId] ?? false);

        return !!$status;
    }

    public function loadHreflangData(CategoryInterface $category): self
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), ['hreflang_category_id'])
            ->where('category_id = ?', $category->getId());
        $category->setData(self::DATA_KEY_LINKS, $connection->fetchCol($select) ?: []);

        return $this;
    }

    public function getHreflangLinks(CategoryInterface $category): array
    {
        if (!$category->hasData(self::DATA_KEY_LINKS)) {
            $this->loadHreflangData($category);
        }

        return $category->getData(self::DATA_KEY_LINKS);
    }

    public function saveHreflangData(CategoryInterface $category): self
    {
        $links = $category->getData(self::DATA_KEY_LINKS);
        if (!is_array($links)) {
            return $this;
        }

        $table = $this->getMainTable();
        $connection = $this->getConnection();
        $connection->delete($table, ['category_id = ?' => $category->getId()]);

        $data = array_map(function ($linkedCategoryId) use ($category) {
            return [
                'category_id' => $category->getId(),
                'hreflang_category_id' => $linkedCategoryId
            ];
        }, $links);

        if ($data) {
            $connection->insertMultiple($table, $data);
        }

        return $this;
    }

    public function getHreflangCategories(CategoryInterface $category): array
    {
        if (!$category->hasData(self::DATA_KEY_CATEGORIES)) {
            $items = [];
            if ($links = $this->getHreflangLinks($category)) {
                $criteria = $this->searchCriteriaBuilder
                    ->addFilter('entity_id', implode(',', $links), 'in')
                    ->create();
                $categoryList = $this->categoryList->getList($criteria);
                $items = $categoryList->getItems();
            }

            $category->setData(self::DATA_KEY_CATEGORIES, $items);
            $this->preloadStatusData($items);
        }

        return $category->getData(self::DATA_KEY_CATEGORIES);
    }

    public function getHreflangCategory(
        CategoryInterface $category,
        $store
    ): ?CategoryInterface {
        $hreflangCategories = $this->getHreflangCategories($category);
        foreach ($hreflangCategories as $hreflangCategory) {
            $parentIds = $hreflangCategory->getParentIds();
            $zeroLevelCategoryId = array_shift($parentIds);
            $rootCatgeoryId = array_shift($parentIds);
            if ($rootCatgeoryId == $store->getRootCategoryId()
                && $this->isEnabled($hreflangCategory, $store)
            ) {
                return $hreflangCategory;
            }
        }

        return null;
    }
}
