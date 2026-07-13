<?php

namespace Swissup\ProLabels\Observer;

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Event;

class ProductSaveAfter implements Event\ObserverInterface
{
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        IndexerRegistry $indexerRegistry
    ) {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * @param  Event\Observer $observer
     * @return void
     */
    public function execute(Event\Observer $observer)
    {
        $product = $observer->getProduct();
        if (!$product) {
            return;
        }

        $indexer = $this->indexerRegistry->get('swissup_prolabels');

        if (!$indexer->isScheduled()) {
            $indexer->reindexRow($product->getId());
        }
    }
}
