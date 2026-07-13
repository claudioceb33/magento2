<?php

namespace Swissup\SeoTemplates\Model\ResourceModel\Template;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    private $storeManager;
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init(
            'Swissup\SeoTemplates\Model\Template',
            'Swissup\SeoTemplates\Model\ResourceModel\Template'
        );
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->performAfterLoad('swissup_seotemplates_store', 'id');
        return parent::_afterLoad();
    }

    /**
     * Perform operations after collection load
     *
     * @param string $tableName
     * @param string $columnName
     * @return void
     */
    protected function performAfterLoad($tableName, $columnName)
    {
        $items = $this->getColumnValues($columnName);
        if (count($items)) {
            $connection = $this->getConnection();
            $select = $connection->select()->from(['store' => $this->getTable($tableName)])
                ->where('store.template_id' . ' IN (?)', $items);
            $result = [];
            foreach ($connection->fetchAll($select) as $row) {
                $result[$row['template_id']][] = $row['store_id'];
            }
            if ($result) {
                foreach ($this as $item) {
                    $entityId = $item->getData($columnName);
                    if (!isset($result[$entityId])) {
                        continue;
                    }
                    if ($result[$entityId] == 0) {
                        $stores = $this->storeManager->getStores(false, true);
                        $storeId = current($stores)->getId();
                        $storeCode = key($stores);
                    } else {
                        $storeId = reset($result[$item->getData($columnName)]);
                    }
                    $item->setData('_first_store_id', $storeId);
                    $item->setData('store_id', $result[$entityId]);
                }
            }
        }
    }
}
