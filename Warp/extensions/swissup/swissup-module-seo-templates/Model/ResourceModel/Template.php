<?php

namespace Swissup\SeoTemplates\Model\ResourceModel;

class Template extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('swissup_seotemplates_template', 'id');
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        //  1. SAVE STORES FOR TEMPLATE
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array) $object->getStores();
        if (empty($newStores)) {
            $newStores = (array) $object->getStoreId();
        }

        $table  = $this->getTable('swissup_seotemplates_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);
        if ($delete) {
            $where = array(
                'template_id = ?' => (int) $object->getId(),
                'store_id IN (?)' => $delete
            );
            $this->getConnection()->delete($table, $where);
        }

        if ($insert) {
            $data = array();
            foreach ($insert as $storeId) {
                $data[] = array(
                    'template_id'  => (int) $object->getId(),
                    'store_id' => (int) $storeId
                );
            }
            $this->getConnection()->insertMultiple($table, $data);
        }

        return parent::_afterSave($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId()) {
            // get stores assigned to template
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);
        }

        return parent::_afterLoad($object);
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $templateId
     * @return array
     */
    public function lookupStoreIds($templateId)
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable('swissup_seotemplates_store'), 'store_id')
            ->where('template_id = ?', (int) $templateId);
        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Clear log records for template with ID $templateId
     *
     * @param  int   $templateId
     * @param  array $entityIds
     * @return void
     */
    public function clearLog($templateId, $entityIds = [])
    {
        try {
            $where = ['template_id = ' . $templateId];
            if ($entityIds) {
                $where[] = 'entity_id IN (' . implode(',', $entityIds) . ')';
            }

            $connection = $this->getConnection();
            $connection->beginTransaction();
            $connection->delete(
                $this->getTable('swissup_seotemplates_log'),
                implode(' AND ', $where)
            );
            $connection->commit();
        } catch (\Exception $exception) {
            $connection->rollback();
        }
    }
}
