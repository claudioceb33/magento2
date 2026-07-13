<?php

namespace Swissup\ProLabelsConfigurableProduct\Model\ResourceModel\Label;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Configurable extends AbstractDb
{
    /**
     * @var bool
     */
    protected $_isPkAutoIncrement = false;

    /**
     * Init resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_prolabels_label_configurable', 'label_id');
    }

    public function getChildLabels(
        $parentId,
        $storeId = 0,
        $customerGroupId = 0,
        $mode = 'product'
    ) {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['s' => $this->getTable('catalog_product_super_link')],
            ['s.parent_id']
        );
        $select->join(
            ['i' => $this->getTable('swissup_prolabels_index')],
            'i.entity_id = s.product_id',
            []
        );
        $select->join(
            ['l' => $this->getTable('swissup_prolabels_label')],
            'l.label_id = i.label_id',
            [
                'sort_order' => "l.sort_order",
                'position' => "l.{$mode}_position",
                'text' => "l.{$mode}_text",
                'custom' => "l.{$mode}_custom_style",
                'custom_url' => "l.{$mode}_custom_url",
                'round_method' => "l.{$mode}_round_method",
                'round_value' => "l.{$mode}_round_value",
                'image' => "l.{$mode}_image",
                'target_element' => "l.{$mode}_target_element",
                'insert_method' => "l.{$mode}_insert_method"
            ]
        );
        $select->join(
            ['c' => $this->getMainTable()],
            'c.label_id = l.label_id',
            []
        );
        $select->where('s.parent_id IN (?)', $parentId);
        $select->where("c.{$mode}_show_child_labels = ?", 1);
        $select->where('status = ?', '1');
        $select->where('store_id LIKE \'%\\"0\\"%\' OR store_id LIKE ?', "%\"{$storeId}\"%");
        $select->where('customer_groups LIKE ?', "%\"{$customerGroupId}\"%");
        $select->distinct();

        $labels = [];
        foreach ($connection->fetchAll($select) as $data) {
            if (empty($data['text']) && empty($data['custom']) && empty($data['image'])) {
                continue;
            }
            $parentId = $data['parent_id'];
            unset($data['parent_id']);
            $labels[$parentId][] = new \Magento\Framework\DataObject($data);
        }

        return $labels;
    }

    public function read($labelId): array
    {
        $connection = $this->getConnection();
        $select = $this->_getLoadSelect('label_id', $labelId, null);

        return $connection->fetchAssoc($select) ?: [];
    }

    public function write($object): void
    {
        $data = $this->_prepareDataForSave($object);
        $fieldsToUpdate = array_keys($data);
        unset($fieldsToUpdate['label_id']);
        $this->getConnection()->insertOnDuplicate(
            $this->getMainTable(),
            $data,
            $fieldsToUpdate
        );
    }
}
