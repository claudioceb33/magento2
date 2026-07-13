<?php

namespace Swissup\ProLabels\Model;

class Indexer implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var ResourceModel\Index
     */
    private $indexResource;

    /**
     * @param ResourceModel\Index $indexResource
     */
    public function __construct(ResourceModel\Index $indexResource)
    {
        $this->indexResource = $indexResource;
    }

    /**
     * Execute
     *
     * @param null|int|array $ids
     * @return void
     */
    public function execute($ids)
    {
        $ids = is_array($ids) ? $ids : [$ids];
        $children = $this->indexResource->getChildrenIdsForSuperProduct($ids);
        $ids = array_merge($ids, $children);

        $this->indexResource->cleanIndexes($ids);
        $this->indexResource->buildIndexes($ids);
    }

    /**
     * Execute full index action
     *
     * @return void
     */
    public function executeFull()
    {
        $this->indexResource->cleanIndexes();
        $this->indexResource->buildIndexes();
    }

    /**
     * Execute partial index action by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->execute($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
         $this->execute($id);
    }
}
