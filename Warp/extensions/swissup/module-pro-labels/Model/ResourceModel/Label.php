<?php

namespace Swissup\ProLabels\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * ProLabels Label mysql resource
 */
class Label extends \Magento\Rule\Model\ResourceModel\AbstractResource
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_prolabels_label', 'label_id');
    }

    public function getIndexedProducts($id)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('swissup_prolabels_index'),
            'entity_id'
        )->where(
            'label_id = :label_id'
        );
        $binds = [':label_id' => (int)$id];
        return $connection->fetchCol($select, $binds);
    }

    public function getProductLabels(
        $productId,
        $storeId = 0,
        $customerGroupId = 0,
        $mode = 'product'
    ) {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['i' => $this->getTable('swissup_prolabels_index')],
            ['i.entity_id']
        );
        $select->joinLeft(
            ['l' => $this->getMainTable()],
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
        $select->where('entity_id IN (?)', $productId);
        $select->where('status = ?', '1');
        $select->where('store_id LIKE \'%\\"0\\"%\' OR store_id LIKE ?', "%\"{$storeId}\"%");
        $select->where('customer_groups LIKE ?', "%\"{$customerGroupId}\"%");

        $labels = [];
        foreach ($connection->fetchAll($select) as $data) {
            if (empty($data['text']) && empty($data['custom']) && empty($data['image'])) {
                continue;
            }
            $productId = $data['entity_id'];
            unset($data['entity_id']);
            $labels[$productId][] = new \Magento\Framework\DataObject($data);
        }

        return $labels;
    }

    /**
     * @param  AbstractModel $label
     * @return AbstractModel
     */
    public function duplicate(AbstractModel $label)
    {
        $newLabel = clone $label;
        $this->saveNewObject($newLabel);

        return $newLabel;
    }
}
