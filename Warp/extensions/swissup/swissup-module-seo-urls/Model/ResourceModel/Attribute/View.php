<?php

namespace Swissup\SeoUrls\Model\ResourceModel\Attribute;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class View extends AbstractDb
{
    /**
     * @var array
     */
    private $cacheValues = [];

    /**
     * @var array
     */
    private $cacheAdvanced = [];

    /**
     * @var array
     */
    private $cacheAttribute;

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('eav_attribute', 'attribute_id');
    }

    /**
     * Read in_URL values for $originalValue of attribute with ID $attributeId
     *
     * @param  int    $attributeId
     * @param  string $originalValue
     * @return array
     */
    public function getInUrlValues($attributeId, $originalValue)
    {
        $attributeValues = $this->_getInUrlValues([$attributeId]);

        return $attributeValues[$attributeId][$originalValue] ?? [];
    }

    /**
     * @param  array $attributeIds
     * @return array
     */
    private function _getInUrlValues(array $attributeIds)
    {
        if (array_diff($attributeIds, array_keys($this->cacheValues))) {
            $select = $this->getConnection()->select()
                ->from(['v' => $this->getTable('swissup_seourls_attribute_value')])
                ->where('attribute_id IN (?)', $attributeIds)
                ->order(['attribute_id', 'original_value', 'store_id']);
            $data = $this->getConnection()->fetchAll($select);
            foreach ($attributeIds as $id) {
                $this->cacheValues[$id] = [];
            }

            foreach ($data as $item) {
                $id = $item['attribute_id'];
                $originalValue = $item['original_value'];
                $storeId = $item['store_id'];
                $this->cacheValues[$id][$originalValue][$storeId] = array_intersect_key(
                    $item,
                    array_flip([
                        'store_id',
                        'url_value'
                    ])
                );
            }
        }

        return array_intersect_key($this->cacheValues, array_flip($attributeIds));
    }

    /**
     * @param  int $attributeId
     * @return $this
     */
    public function flushCachedValues($attributeId)
    {
        unset($this->cacheValues[$attributeId]);

        return $this;
    }

    /**
     * Read in-url attribute labels from DB
     *
     * @param  int   $attributeId
     * @return array
     */
    public function getInUrlLabels(\Magento\Framework\DataObject $attribute)
    {
        $attributeData = $this->_getAttributes();
        if ($attribute->getId()) {
            $attributeId = $attribute->getId();
        } elseif ($attribute->getAttributeCode()) {
            $attributeId = 0;
            foreach ($attributeData as $id => $data) {
                if ($data['code'] == $attribute->getAttributeCode()) {
                    $attributeId = $id;
                    break;
                }
            }
        }

        return $attributeData[$attributeId]['labels'] ?? [];
    }

    /**
     * @return array
     */
    private function _getAttributes()
    {
        if (!isset($this->cacheAttribute)) {
            $select = $this->getConnection()->select()
                ->from(
                    ['l' => $this->getTable('swissup_seourls_attribute_label')],
                    ['attribute_id', 'store_id', 'value']
                )
                ->joinLeft(
                    ['eav' => $this->getMainTable()],
                    'eav.attribute_id = l.attribute_id',
                    ['attribute_code']
                );
            $data = $this->getConnection()->fetchAll($select);
            $this->cacheAttribute = [];
            foreach ($data as $item) {
                $id = $item['attribute_id'];
                $storeId = $item['store_id'];
                $this->cacheAttribute[$id]['code'] = $item['attribute_code'];
                $this->cacheAttribute[$id]['labels'][$storeId] = array_intersect_key(
                    $item,
                    array_flip([
                        'store_id',
                        'value'
                    ])
                );
            }
        }

        return $this->cacheAttribute;
    }

    /**
     * @return $this
     */
    public function flushCachedLabels()
    {
        unset($this->cacheAttribute);

        return $this;
    }

    /**
     * Get advanced properties values
     *
     * @param  \Magento\Framework\DataObject $attribute
     * @return array
     */
    public function getAdvancedProps(\Magento\Framework\DataObject $attribute)
    {
        $advanced = $this->_getAdvanced([$attribute->getId()]);

        return $advanced[$attribute->getId()] ?? [];
    }

    /**
     * @param  array  $attributeIds
     * @return array
     */
    private function _getAdvanced(array $attributeIds)
    {
        if (array_diff($attributeIds, array_keys($this->cacheAdvanced))) {
            $select = $this->getConnection()->select()
                ->from(['a' => $this->getTable('swissup_seourls_attribute_advanced')])
                ->where('attribute_id IN (?)', $attributeIds);

            foreach ($attributeIds as $id) {
                $this->cacheAdvanced[$id] = [];
            }

            $this->cacheAdvanced = $this->getConnection()->fetchAssoc($select)
                + $this->cacheAdvanced;
        }

        return array_intersect_key($this->cacheAdvanced, array_flip($attributeIds));
    }

    /**
     * @param  int $attributeId
     * @return $this
     */
    public function flushCachedAdvancedProps($attributeId)
    {
        unset($this->cacheAdvanced[$attributeId]);

        return $this;
    }

    /**
     * Preload (warm up) extension DB data for product attributes.
     *
     * @param  array  $attributeIds
     * @return void
     */
    public function preloadData(array $attributeIds)
    {
        $this->_getAttributes();
        $this->_getInUrlValues($attributeIds);
        $this->_getAdvanced($attributeIds);
    }
}
