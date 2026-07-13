<?php

namespace Swissup\SeoImages\Model\ResourceModel;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Index extends AbstractDb
{
    /**
     * @var ProductRepositoryInterface;
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param ProductRepositoryInterface                        $productRepository
     * @param SearchCriteriaBuilder                             $searchCriteriaBuilder
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param int                                               $batchSize
     * @param string                                            $connectionName
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $batchSize = 200,
        $connectionName = null
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->batchSize = $batchSize;
        parent::__construct($context, $connectionName);
    }

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_seoimages_index', 'id');
    }

    /**
     * Delete indexes by product IDs.
     *
     * @param  array  $ids
     */
    public function deleteIndex(array $ids = [])
    {
        if (empty($ids)) {
            $this->getConnection()->truncateTable($this->getMainTable());
        } else {
            $this->getConnection()->delete(
                $this->getMainTable(),
                ['entity_id IN (?)' => $ids]
            );
        }

        return $this;
    }

    /**
     * Build indexes for product IDs.
     *
     * @param  array  $ids
     */
    public function saveIndex(array $ids = [])
    {
        $page = 1;
        do {
            if (!empty($ids)) {
                $criteria = $this->searchCriteriaBuilder
                    ->addFilter('entity_id', implode(',', $ids), 'in')
                    ->setPageSize($this->batchSize)
                    ->setCurrentPage($page)
                    ->create();
            } else {
                $criteria = $this->searchCriteriaBuilder
                    ->setPageSize($this->batchSize)
                    ->setCurrentPage($page)
                    ->create();
            }

            $result = $this->productRepository->getList($criteria);
            $totalPages = ceil($result->getTotalCount() / $this->batchSize);
            $data = [];
            foreach ($result->getItems() as $product) {
                $images = $product->getData('media_gallery/images');
                $images = is_array($images) ? $images : [];
                foreach ($images as $image) {
                    $data[] = [
                        'entity_id' => $product->getId(),
                        'file' => $image['file']
                    ];
                }
            }

            $this->insertData($data);

            $page++;
        } while ($totalPages >= $page);

        return $this;
    }

    /**
     * Count indexed images names.
     *
     * @return int
     */
    public function countImages()
    {
        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from(
                $this->getMainTable(),
                'count(DISTINCT file, file)'
            );
        $data = $connection->fetchRow($select);

        return is_array($data) ? (int)reset($data) : 0;
    }

    /**
     * @param  array  &$data
     * @return $this
     */
    private function insertData(array &$data)
    {
        if (empty($data)) {
            return $this;
        }

        $this->getConnection()->insertMultiple($this->getMainTable(),$data);

        return $this;
    }
}
