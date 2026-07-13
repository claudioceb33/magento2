<?php

namespace Swissup\SeoUrls\Model\ResourceModel\Attribute;

use Magento\Framework\Model\ResourceModel\Db;
use Magento\Framework\DataObject;
use Swissup\SeoUrls\Model\Attribute\AdvancedFactory;

class Action extends Db\AbstractDb
{
    /**
     * @var AdvancedFactory
     */
    protected $attributeAdvancedFactory;

    /**
     * @var View
     */
    protected $view;

    /**
     * @param View            $view
     * @param AdvancedFactory $attributeAdvancedFactory
     * @param Db\Context      $context
     * @param string          $connectionName
     */
    public function __construct(
        View $view,
        AdvancedFactory $attributeAdvancedFactory,
        Db\Context $context,
        $connectionName = null
    ) {
        $this->view = $view;
        $this->attributeAdvancedFactory = $attributeAdvancedFactory;
        parent::__construct($context, $connectionName);
    }

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
    }

    /**
     * Update in-url labels for attribute object $attribute
     *
     * @param  \Magento\Framework\DataObject $attribute
     * @param  array                         $newLabels
     * @param  bool                          $clearMemorized
     */
    public function updateInUrlLabels(
        \Magento\Framework\DataObject $attribute,
        array $newLabels = [],
        $clearMemorized = true
    ) {
        $newLabels = array_filter($newLabels);
        $oldLabels = [];
        foreach ($this->view->getInUrlLabels($attribute) as $label) {
            $oldLabels[$label['store_id']] = $label['value'];
        }

        $table = $this->getTable('swissup_seourls_attribute_label');
        $insert = array_diff_assoc($newLabels, $oldLabels);
        $delete = array_diff_assoc($oldLabels, $newLabels);
        if ($delete) {
            $where = array(
                'attribute_id = ?' => (int) $attribute->getId(),
                'store_id IN (?)' => array_keys($delete)
            );
            $this->getConnection()->delete($table, $where);
        }

        if ($insert) {
            $data = [];
            foreach ($insert as $storeId => $value) {
                $data[] = [
                    'attribute_id'  => (int) $attribute->getId(),
                    'store_id' => (int) $storeId,
                    'value' => $value
                ];
            }

            $this->getConnection()->insertMultiple($table, $data);
        }

        if ($clearMemorized) {
            $this->view->flushCachedLabels();
        }
    }

    /**
     * Get in_URL values for $originalValue of attribute with ID $attributeId
     *
     * @param  int    $attributeId
     * @param  string $originalValue
     * @param  array  $newValues
     */
    public function updateInUrlValues(
        $attributeId,
        $originalValue,
        array $newValues = []
    ) {
        $newValues = array_filter($newValues);
        $oldValues = [];
        foreach ($this->view->getInUrlValues($attributeId, $originalValue) as $value) {
            $oldValues[$value['store_id']] = $value['url_value'];
        }
        // $this->getInUrlValues($attributeId, $originalValue);

        $table = $this->getTable('swissup_seourls_attribute_value');
        $insert = array_diff_assoc($newValues, $oldValues);
        $delete = array_diff_assoc($oldValues, $newValues);
        if ($delete) {
            $where = [
                'attribute_id = ?' => (int) $attributeId,
                'original_value = ?' => $originalValue,
                'store_id IN (?)' => array_keys($delete)
            ];
            $this->getConnection()->delete($table, $where);
        }

        if ($insert) {
            $data = [];
            foreach ($insert as $storeId => $value) {
                $data[] = [
                    'attribute_id'  => (int) $attributeId,
                    'store_id' => (int) $storeId,
                    'original_value' => $originalValue,
                    'url_value' => $value
                ];
            }

            $this->getConnection()->insertMultiple($table, $data);
        }

        $this->view->flushCachedValues($attributeId);
    }

    /**
     * Update advanced properties for attribute
     *
     * @param  \Magento\Framework\DataObject $attribute
     * @param  array                         $newValues
     */
    public function updateAdvacedProps(
        \Magento\Framework\DataObject $attribute,
        array $newValues
    ) {
        $attributeAdvanced = $this->attributeAdvancedFactory->create();
        $attributeAdvanced->load($attribute->getId())
            ->addData($newValues)
            ->setId($attribute->getId())
            ->save();

        $this->view->flushCachedAdvancedProps($attribute->getId());
    }
}
