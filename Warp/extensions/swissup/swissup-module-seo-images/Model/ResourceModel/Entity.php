<?php

namespace Swissup\SeoImages\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Entity extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected $_isPkAutoIncrement = false;

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_seoimages', 'file_name');
    }

    /**
     * {@inheritdoc}
     */
    public function load(
        \Magento\Framework\Model\AbstractModel $object,
        $value,
        $field = null
    ) {
        // All fields in table has MAX_LENGTH = 255.
        $value = substr($value, 0, 255);
        return parent::load($object, $value, $field);
    }

    /**
     * Count unique pairs of generated name and original file name.
     *
     * @return int
     */
    public function countUniquePairs()
    {
        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from(
                $this->getMainTable(),
                'count(DISTINCT original_file, target_file)'
            );
        $data = $connection->fetchRow($select);

        return is_array($data) ? (int)reset($data) : 0;
    }

    /**
     * Clean save generated names
     *
     * @return void
     */
    public function cleanCached()
    {
        $connection = $this->getConnection();
        $connection->truncateTable($this->getMainTable());
    }
}
