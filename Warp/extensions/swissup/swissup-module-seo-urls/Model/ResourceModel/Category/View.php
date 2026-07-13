<?php

namespace Swissup\SeoUrls\Model\ResourceModel\Category;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class View extends AbstractDb
{
    /**
     * @var array
     */
    protected $cache = [];

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_category_entity', 'entity_id');
    }

    /**
     * Read in-url category labels from DB
     *
     * @param  \Magento\Framework\DataObject $cartegory
     * @return array
     */
    public function getInUrlLabels(\Magento\Framework\DataObject $category)
    {
        $categoryData = $this->_getInUrlValues([$category->getId()]);

        return $categoryData[$category->getId()] ?? [];
    }

    /**
     * @param  array  $categoryIds
     * @return array
     */
    private function _getInUrlValues(array $categoryIds)
    {
        $categoryIds = array_filter($categoryIds);
        if (array_diff($categoryIds, array_keys($this->cache))) {
            $select = $this->getConnection()->select()
                ->from(
                    ['l' => $this->getTable('swissup_seourls_category_label')],
                    ['entity_id', 'store_id', 'value']
                )
                ->where('entity_id IN (?)', $categoryIds);
            $data = $this->getConnection()->fetchAll($select);
            foreach ($categoryIds as $id) {
                $this->cache[$id] = [];
            }

            foreach ($data as $item) {
                $id = $item['entity_id'];
                $value = $item['value'];
                $storeId = $item['store_id'];
                $this->cache[$id][$storeId] = array_intersect_key(
                    $item,
                    array_flip([
                        'store_id',
                        'value'
                    ])
                );
            }
        }

        return array_intersect_key($this->cache, array_flip($categoryIds));
    }

    /**
     * @param  int $categoryId
     * @return void
     */
    public function flushCached($categoryId)
    {
        unset($this->cache[$categoryId]);
    }

    /**
     * Preload (warm up) extension DB data for categories.
     *
     * @param  array  $categoryIds
     * @return void
     */
    public function preloadData(array $categoryIds)
    {
        $this->_getInUrlValues($categoryIds);
    }
}
