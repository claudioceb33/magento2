<?php

namespace Ceb\Reindex\Cron;

use Magento\Indexer\Model\IndexerFactory;

class ReindexProducts
{
    protected $indexerFactory;

    public function __construct(
        IndexerFactory $indexerFactory
    ) {
        $this->indexerFactory = $indexerFactory;
    }

    public function execute()
    {
        $indexers = [
            'catalog_product_price',
            'cataloginventory_stock',
            'catalog_product_attribute',
            'catalogsearch_fulltext'
        ];

        foreach ($indexers as $indexerId) {
            $indexer = $this->indexerFactory->create();
            $indexer->load($indexerId);
            $indexer->reindexAll();
        }
    }
}
