<?php

namespace Swissup\SeoUrls\Model\ResourceModel\Category;

use Magento\Framework\Model\ResourceModel\Db;

class Action extends Db\AbstractDb
{
    /**
     * @var View
     */
    private $view;

    /**
     * @param View       $view
     * @param Db\Context $context
     * @param string     $connectionName
     */
    public function __construct(
        View $view,
        Db\Context $context,
        $connectionName = null
    ) {
        $this->view = $view;
        parent::__construct($context, $connectionName);
    }

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_category_entity', 'entity_id');
    }

    /**
     * Update labels data fr category in DB
     *
     * @param  \Magento\Framework\DataObject $category
     * @param  array                         $newLabels
     * @return void
     */
    public function updateInUrlLabels(
        \Magento\Framework\DataObject $category,
        array $newLabels = []
    ) {

        $newLabels = $newLabels;
        $oldLabels = [];
        foreach ($this->view->getInUrlLabels($category) as $label) {
            $oldLabels[$label['store_id']] = $label['value'];
        }

        $newLabels += $oldLabels;

        $table = $this->getTable('swissup_seourls_category_label');
        $insert = array_diff_assoc($newLabels, $oldLabels);
        $delete = array_diff_assoc($oldLabels, $newLabels);
        if ($delete) {
            $where = array(
                'entity_id = ?' => (int) $category->getId(),
                'store_id IN (?)' => array_keys($delete)
            );
            $this->getConnection()->delete($table, $where);
        }

        $insert = array_filter($insert);
        if ($insert) {
            $data = [];
            foreach ($insert as $storeId => $value) {
                $data[] = [
                    'entity_id'  => (int) $category->getId(),
                    'store_id' => (int) $storeId,
                    'value' => $value
                ];
            }

            $this->getConnection()->insertMultiple($table, $data);
        }

        $this->view->flushCached($category->getId());
    }
}
