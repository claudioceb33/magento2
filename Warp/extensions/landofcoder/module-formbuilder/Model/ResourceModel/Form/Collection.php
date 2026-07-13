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

declare(strict_types=1);

namespace Lof\Formbuilder\Model\ResourceModel\Form;

use Lof\Formbuilder\Model\Form;
use Lof\Formbuilder\Model\ResourceModel\AbstractCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'form_id';

    /**
     * @return $this
     * @throws NoSuchEntityException
     */
    protected function _afterLoad(): static
    {
        $this->performAfterLoad('lof_formbuilder_form_store', 'form_id');
        $this->loadCustomerGroup();
        return parent::_afterLoad();
    }

    /**
     * Define resource model
     *
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(Form::class, \Lof\Formbuilder\Model\ResourceModel\Form::class);
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    /**
     * @return void
     */
    protected function loadCustomerGroup(): void
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getTable('lof_formbuilder_form_customergroup'));
        $customerGroups = $connection->fetchAll($select);
        foreach ($this as $item) {
            $groups = [];
            foreach ($customerGroups as $k => $v) {
                if ($v['form_id'] == $item->getId()) {
                    $groups[] = $v['customer_group_id'];
                }
            }
            $item->setData('customer_group_ids', $groups);
            $item->setData('customergroups', $groups);
        }
    }

    /**
     * Returns pairs form_id - title
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return $this->_toOptionArray('form_id', 'title');
    }

    /**
     * Add filter by store
     *
     * @param array|int|Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter(Store|array|int $store, bool $withAdmin = true): static
    {
        $this->performAddStoreFilter($store, $withAdmin);

        return $this;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore(): void
    {
        $this->joinStoreRelationTable('lof_formbuilder_form_store', 'form_id');
    }

    /**
     * Perform operations after collection load
     *
     * @param string $tableName
     * @param string $columnName
     * @return void
     * @throws NoSuchEntityException
     */
    protected function performAfterLoad($tableName, $columnName): void
    {
        $items = $this->getColumnValues($columnName);
        if (count($items)) {
            $connection = $this->getConnection();
            $select = $connection->select()->from(['formbuilder_entity_store' => $this->getTable($tableName)])
                ->where('formbuilder_entity_store.' . $columnName . ' IN (?)', $items);
            $result = $connection->fetchPairs($select);
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
                        $storeId = $result[$item->getData($columnName)];
                        $storeCode = $this->storeManager->getStore($storeId)->getCode();
                    }
                    $item->setData('_first_store_id', $storeId);
                    $item->setData('store_code', $storeCode);
                    $item->setData('store', [$result[$entityId]]);
                    $item->setData('stores', $result[$entityId]);
                    $item->setData('store_id', $result[$entityId]);
                }
            }
        }
    }
}
