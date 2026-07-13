<?php

namespace Swissup\SeoTemplates\Model\ResourceModel;

class Seodata extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected $_serializableFields = ['metadata' => ['', []]];

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('swissup_seotemplates_data', 'id');
    }

    /**
     * Delete all generated data for entity types in $entityTypes
     *
     * @param  array $entityTypes
     * @param  array $entityIds
     * @return void
     */
    public function deleteGenerated($entityTypes = [], $entityIds = [])
    {
        $where = [];
        if ($entityTypes) {
            $where[] = 'entity_type IN (' . implode(',', $entityTypes) . ')';
        }

        if ($entityIds) {
            $where[] = 'entity_id IN (' . implode(',', $entityIds) . ')';
        }

        try {
            $connection = $this->getConnection();
            $connection->beginTransaction();
            $connection->delete(
                $this->getTable('swissup_seotemplates_data'),
                implode(' AND ', $where)
            );
            $connection->commit();
        } catch (\Exception $exception) {
            $connection->rollback();
        }
    }
}
