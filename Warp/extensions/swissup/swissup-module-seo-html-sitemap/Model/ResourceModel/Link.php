<?php
namespace Swissup\SeoHtmlSitemap\Model\ResourceModel;

use \Magento\Framework\Model\AbstractModel;
use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use \Magento\Framework\Model\ResourceModel\Db\Context;
use \Magento\Framework\Stdlib\DateTime\DateTime;
use \Magento\Store\Model\StoreManagerInterface;

class Link extends AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        Context $context,
        DateTime $date,
        StoreManagerInterface $storeManager,
        $resourcePrefix = null)
    {
        parent::__construct($context, $resourcePrefix);
        $this->date = $date;
        $this->storeManager = $storeManager;
    }
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_seohtmlsitemap_links', 'link_id');
    }
    /**
     * Process seohtmlsitemap data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Swissup\SeoHtmlSitemap\Model\ResourceModel\Link
     */
    protected function _beforeDelete(AbstractModel $object)
    {
        $condition = ['link_id = ?' => (int)$object->getId()];
        $this->getConnection()->delete($this->getTable('swissup_seohtmlsitemap_store'), $condition);

        return parent::_beforeDelete($object);
    }
    /**
     * Process seohtmlsitemap data before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if (!$object->getId()) {
            $object->setCreationTime($this->date->gmtDate());
        }

        $object->setUpdateTime($this->date->gmtDate());

        return parent::_beforeSave($object);
    }
    /**
     * Perform operations after object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStoreId();

        if (empty($newStores)) {
            $newStores = (array)$object->getStoreId();
        }

        $table = $this->getTable('swissup_seohtmlsitemap_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);

        if ($delete) {
            $where = ['link_id = ?' => (int)$object->getId(), 'store_id IN (?)' => $delete];
            $this->getConnection()->delete($table, $where);
        }

        if ($insert) {
            $data = [];

            foreach ($insert as $storeId) {
                $data[] = ['link_id' => (int)$object->getId(), 'store_id' => (int)$storeId];
            }

            $this->getConnection()->insertMultiple($table, $data);
        }

        return parent::_afterSave($object);
    }
    /**
     * Perform operations after object load
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);
            $object->setData('stores', $stores);
        }

        return parent::_afterLoad($object);
    }
    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Swissup\SeoHtmlSitemap\Model\Link $object
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $stores = [(int)$object->getStoreId(), \Magento\Store\Model\Store::DEFAULT_STORE_ID];
            $select->join(
                ['sss' => $this->getTable('swissup_seohtmlsitemap_store')],
                $this->getMainTable() . '.link_id = sss.link_id',
                ['store_id'])
            ->where('sss.store_id in (?)', $stores)
            ->order('store_id DESC')
            ->limit(1);
        }

        return $select;
    }
    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($id)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable('swissup_seohtmlsitemap_store'), 'store_id')
        ->where('link_id = :link_id');

        $binds = [':link_id' => (int)$id];

        return $connection->fetchCol($select, $binds);
    }
}
