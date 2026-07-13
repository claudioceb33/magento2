<?php

namespace Swissup\ProLabels\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;

/**
 * ProLabels Index mysql resource
 */
class Index extends Db\AbstractDb
{
    /**
     * @var Configurable
     */
    protected $resourceProductConfigurable;

    /**
     * @var Label\CollectionFactory
     */
    protected $labelCollectionFactory;

    /**
     * Constructor
     *
     * @param Label\CollectionFactory $labelCollectionFactory
     * @param Configurable            $resourceProductConfigurable
     * @param Db\Context              $context
     * @param string                  $connectionName
     */
    public function __construct(
        Label\CollectionFactory $labelCollectionFactory,
        Configurable $resourceProductConfigurable,
        Db\Context $context,
        $connectionName = null
    ) {
        $this->labelCollectionFactory = $labelCollectionFactory;
        $this->resourceProductConfigurable = $resourceProductConfigurable;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_prolabels_index', 'index_id');
    }

    /**
     * Clean indexes for productIds[]
     *
     * @param  array  $productIds
     * @return $this
     */
    public function cleanIndexes($productIds = [])
    {
        $whereClause = empty($productIds)
            ? null // delete all when no product ids provided
            : ['entity_id IN (?)' => $productIds];
        try {
            $this->getConnection()->delete(
                $this->getMainTable(),
                $whereClause
            );
        } catch (\Exception $e) {
            return $this;
        }

        return $this;
    }

    /**
     * Clean indexes for labels with Ids from $labelIds
     *
     * @param  int|array  $labelIds
     * @return
     */
    public function cleanLabelIndex($labelIds)
    {
        try {
            $this->getConnection()->delete(
                $this->getMainTable(),
                ['label_id IN (?)' => $labelIds]
            );
        } catch (\Exception $e) {
            return $this;
        }

        return $this;
    }

    /**
     * Get IDs of children for super product
     *
     * @param  int|array $superId
     * @return array
     */
    public function getChildrenIdsForSuperProduct($superId)
    {
        $groups = $this->resourceProductConfigurable->getChildrenIds($superId);
        $ids = [];
        foreach ($groups as $children) {
            $ids = array_merge($ids, $children);
        }

        return array_unique($ids);
    }

    /**
     * Build indexes for $labelIds and products with IDs from $productIds
     *
     * @param  array  $productIds
     */
    public function buildIndexes($productIds = [], $labelIds = [])
    {
        $labels = $this->labelCollectionFactory->create();
        if ($labelIds) {
            $labels->addFieldToFilter('label_id', ['in' => $labelIds]);
        }

        foreach ($labels as $label) {
            $matchingData = [];
            $matchingProducts = $label->getMatchingProductIds($productIds);
            if (count($matchingProducts) > 0) {
                $connection = $this->getConnection();
                try {
                    $connection->insertMultiple($this->getMainTable(), $matchingProducts);
                } catch (\Exception $e) {
                    return;
                }
            }
        }
    }

    /**
     * Get number of products.
     *
     * @return int
     */
    public function countProducts()
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from(
            $this->getTable('catalog_product_entity'),
            new \Zend_Db_Expr('COUNT(1)')
        );

        return (int) $connection->fetchOne($select);
    }

    /**
     * @param  int $count
     * @param  int $step
     * @return array
     */
    public function getProductIds($count, $step)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from(
            $this->getTable('catalog_product_entity'),
            'entity_id'
        )->order('entity_id')
        ->limit($count, $count * $step);

        return $connection->fetchCol($select);
    }

    /**
     * @return array
     */
    public function getAllLabelsIds()
    {
        $labels = $this->labelCollectionFactory->create();

        return $labels->getAllIds();
    }
}
