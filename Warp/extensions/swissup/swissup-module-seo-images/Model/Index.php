<?php

namespace Swissup\SeoImages\Model;

class Index extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Swissup\SeoImages\Model\ResourceModel\Index::class);
    }

    /**
     * Execute
     *
     * @param null|int|array $ids
     * @return void
     */
    public function execute($ids)
    {
        $this->_getResource()->deleteIndex($ids);
        $this->_getResource()->saveIndex($ids);
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $this->execute([]);
    }

    /**
     * Execute partial indexation by ID list
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
         $this->execute([$id]);
    }
}
