<?php

namespace Swissup\SeoTemplates\Plugin\Indexer;

use Magento\Framework\Indexer\IndexerRegistry;

abstract class AbstarctPlugin
{
    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        IndexerRegistry $indexerRegistry
    ) {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Reindex by entity if indexer is not scheduled
     *
     * @param int $entityId
     * @return void
     */
    protected function reindexRow($entityId)
    {
        $indexer = $this->indexerRegistry->get($this->getIndexId());

        if (!$indexer->isScheduled()) {
            $indexer->reindexRow($entityId);
        }
    }

    /**
     * @return string
     */
    abstract public function getIndexId();
}
