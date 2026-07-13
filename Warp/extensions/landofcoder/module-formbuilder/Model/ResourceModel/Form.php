<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_Formbuilder
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\Formbuilder\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Db_Select;

class Form extends AbstractDb
{
    /**
     * Store model
     *
     * @var null|Store
     */
    protected $store = null;
    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param $connectionName
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('lof_formbuilder_form', 'form_id');
    }

    /**
     * @param AbstractModel $object
     * @return Form
     */
    protected function _beforeDelete(AbstractModel $object): Form
    {
        $condition = ['form_id = ?' => (int)$object->getId()];

        $this->getConnection()->delete($this->getTable('lof_formbuilder_form_store'), $condition);

        return parent::_beforeDelete($object);
    }

    /**
     * @param AbstractModel $object
     * @return $this|Form
     * @throws LocalizedException
     */
    protected function _beforeSave(AbstractModel $object): Form|static
    {

        $fields = json_decode('[' . $object->getData('design') . ']', true);
        $fields = $fields[0]['fields'] ?? ($fields[0] ?? []);
        if (!empty($fields)) {
            $object->setData('design', json_encode($fields));
        }
        if (!$this->getIsUniqueBlockToStores($object)) {
            throw new LocalizedException(
                __('A form identifier with the same properties already exists in the selected store.')
            );
        }
        return $this;
    }

    /**
     * Perform operations after object save
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object): static
    {
        // STORE
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();
        $table = $this->getTable('lof_formbuilder_form_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);
        if ($delete) {
            $where = ['form_id = ?' => (int)$object->getId(), 'store_id IN (?)' => $delete];
            $this->getConnection()->delete($table, $where);
        }
        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = ['form_id' => (int)$object->getId(), 'store_id' => (int)$storeId];
            }
            $this->getConnection()->insertMultiple($table, $data);
        }

        // CUSTOMER GROUP
        $oldCustomerGroups = $this->lookupCustomerGroupIds($object->getId());
        $newCustomerGroups = (array)$object->getCustomerGroupIds();
        $table = $this->getTable('lof_formbuilder_form_customergroup');
        $insert = array_diff($newCustomerGroups, $oldCustomerGroups);
        $delete = array_diff($oldCustomerGroups, $newCustomerGroups);
        if ($delete) {
            $where = ['form_id = ?' => (int)$object->getId(), 'customer_group_id IN (?)' => $delete];
            $this->getConnection()->delete($table, $where);
        }
        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = ['form_id' => (int)$object->getId(), 'customer_group_id' => (int)$storeId];
            }
            $this->getConnection()->insertMultiple($table, $data);
        }

        return parent::_afterSave($object);
    }

    /**
     * @param AbstractModel $object
     * @param $value
     * @param $field
     * @return Form
     */
    public function load(AbstractModel $object, $value, $field = null): Form
    {
        if (!is_numeric($value) && $field === null) {
            $field = 'identifier';
        }

        return parent::load($object, $value, $field);
    }

    /**
     * Set store model
     *
     * @param Store $store
     * @return $this
     */
    public function setStore(Store $store): static
    {
        $this->store = $store;
        return $this;
    }

    /**
     * @return Store|bool|null
     */
    public function getStore(): Store|bool|null
    {
        if ($this->store) {
            return $this->store;
        }
        return false;
    }

    /**
     * Perform operations after object load
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object): static
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);
            $object->setData('stores', $stores);
            $groups = $this->lookupCustomerGroupIds($object->getId());
            $object->setData('customer_group_ids', $groups);
            $object->setData('customergroups', $groups);
        }
        return parent::_afterLoad($object);
    }

    /**
     * @param $field
     * @param $value
     * @param $object
     * @return Select
     * @throws LocalizedException
     */
    protected function _getLoadSelect($field, $value, $object): Select
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $stores = [(int)$object->getStoreId(), Store::DEFAULT_STORE_ID];

            $select->join(
                ['cbs' => $this->getTable('lof_formbuilder_form_store')],
                $this->getMainTable() . '.form_id = cbs.form_id',
                ['store_id']
            )->where(
                'status = ?',
                1
            )->where(
                'cbs.store_id in (?)',
                $stores
            )->order(
                'store_id DESC'
            )->limit(
                1
            );
        }

        return $select;
    }

    /**
     * @param AbstractModel $object
     * @return bool
     * @throws LocalizedException
     */
    public function getIsUniqueBlockToStores(AbstractModel $object): bool
    {
        if ($this->storeManager->hasSingleStore()) {
            $stores = [Store::DEFAULT_STORE_ID];
        } else {
            $stores = (array)$object->getData('stores');
        }

        $select = $this->getConnection()->select()->from(
            ['cb' => $this->getMainTable()]
        )->join(
            ['cbs' => $this->getTable('lof_formbuilder_form_store')],
            'cb.form_id = cbs.form_id',
            []
        )->where(
            'cb.identifier = ?',
            $object->getData('identifier')
        )->where(
            'cbs.store_id IN (?)',
            $stores
        );

        if ($object->getId()) {
            $select->where('cb.form_id <> ?', $object->getId());
        }

        if ($this->getConnection()->fetchRow($select)) {
            return false;
        }

        return true;
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds(int $id): array
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable('lof_formbuilder_form_store'),
            'store_id'
        )->where(
            'form_id = :form_id'
        );

        $binds = [':form_id' => (int)$id];

        return $connection->fetchCol($select, $binds);
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupCustomerGroupIds(int $id): array
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable('lof_formbuilder_form_customergroup'),
            'customer_group_id'
        )->where(
            'form_id = :form_id'
        );

        $binds = [':form_id' => (int)$id];

        return $connection->fetchCol($select, $binds);
    }

    /**
     * @param $identifier
     * @param $storeId
     * @return int
     * @throws LocalizedException
     */
    public function checkIdentifier($identifier, $storeId): int
    {
        $stores = [Store::DEFAULT_STORE_ID, $storeId];
        $select = $this->getLoadByIdentifierSelect($identifier, $stores, 1);
        $select->reset(Zend_Db_Select::COLUMNS)->columns('cp.form_id')->order('cps.store_id DESC')->limit(1);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * @param string $identifier
     * @param int $customerGroupId
     * @return int
     * @throws LocalizedException
     */
    public function checkCustomerGroup(string $identifier, int $customerGroupId): int
    {
        $groups = [0, $customerGroupId];
        $select = $this->getLoadByCustomerGroupSelect($identifier, $groups, 1);
        $select->reset(Zend_Db_Select::COLUMNS)->columns('cp.form_id')->order('cpg.customer_group_id DESC')->limit(1);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * @param $identifier
     * @param $store
     * @param $isActive
     * @return Select
     * @throws LocalizedException
     */
    protected function getLoadByIdentifierSelect($identifier, $store, $isActive = null): Select
    {
        $select = $this->getConnection()->select()->from(
            ['cp' => $this->getMainTable()]
        )->join(
            ['cps' => $this->getTable('lof_formbuilder_form_store')],
            'cp.form_id = cps.form_id',
            []
        )->where(
            'cp.identifier = ?',
            $identifier
        )->where(
            'cps.store_id IN (?)',
            $store
        );

        if (!is_null($isActive)) {
            $select->where('cp.status = ?', $isActive);
        }

        return $select;
    }

    /**
     * @param $identifier
     * @param $groupId
     * @param $isActive
     * @return Select
     * @throws LocalizedException
     */
    protected function getLoadByCustomerGroupSelect($identifier, $groupId, $isActive = null): Select
    {
        $select = $this->getConnection()->select()->from(
            ['cp' => $this->getMainTable()]
        )->join(
            ['cpg' => $this->getTable('lof_formbuilder_form_customergroup')],
            'cp.form_id = cpg.form_id',
            []
        )->where(
            'cp.identifier = ?',
            $identifier
        )->where(
            'cpg.customer_group_id IN (?)',
            $groupId
        );

        if (!is_null($isActive)) {
            $select->where('cp.status = ?', $isActive);
        }

        return $select;
    }
}
