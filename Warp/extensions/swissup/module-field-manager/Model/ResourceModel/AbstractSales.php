<?php
namespace Swissup\FieldManager\Model\ResourceModel;

abstract class AbstractSales extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Primary key auto increment flag
     *
     * @var bool
     */
    protected $_isPkAutoIncrement = false;

    /**
     * @var \Swissup\FieldManager\Helper\Data
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Swissup\FieldManager\Helper\Data $helper
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Swissup\FieldManager\Helper\Data $helper,
        $connectionName = null
    ) {
        $this->helper = $helper;
        parent::__construct($context, $connectionName);
    }

    public function saveNewAttribute(\Magento\Customer\Model\Attribute $attribute)
    {
        $columnName = $attribute->getAttributeCode();
        if ($this->getConnection()->tableColumnExists($this->getMainTable(), $columnName)) {
            return $this;
        }

        $columnProps = $this->helper->getColumnType($attribute->getBackendType());
        if (isset($columnProps)) {
            $columnProps['comment'] = $columnName;
            $this->getConnection()->addColumn($this->getMainTable(), $columnName, $columnProps);
        }

        return $this;
    }

    public function deleteAttribute(\Magento\Customer\Model\Attribute $attribute)
    {
        $this->getConnection()->dropColumn($this->getMainTable(), $attribute->getAttributeCode());

        return $this;
    }

    /**
     * @return bool
     */
    public function checkMainEntity($model)
    {
        if (!$model->getId()) {
            return false;
        }

        $entityTable = $this->getParentResourceModel()->getMainTable();
        $entityIdField = $this->getParentResourceModel()->getIdFieldName();
        $select = $this->getConnection()->select()->from(
            $entityTable,
            $entityIdField
        )->forUpdate(
            true
        )->where(
            "{$entityIdField} = ?",
            $model->getId()
        );

        if ($this->getConnection()->fetchOne($select)) {
            return true;
        }

        return false;
    }

    /**
     * @param array $ids
     */
    public function loadByIds($ids)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable()
        )->where(
            "{$this->getIdFieldName()} IN (?)",
            $ids
        );

        return $this->getConnection()->fetchAll($select);
    }

    abstract protected function getParentResourceModel();
}
