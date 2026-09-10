<?php

declare(strict_types=1);

namespace Ceb\ErpStockSync\Model\Stock;

use Ceb\ErpStockSync\Api\ErpStockClientInterface;
use Ceb\ErpStockSync\Api\ProductSkuValidatorInterface;
use Ceb\ErpStockSync\Api\StockSynchronizerInterface;
use Ceb\ErpStockSync\Api\StockBatchUpdaterInterface;
use Ceb\ErpStockSync\Model\Config;

class StockSynchronizer implements StockSynchronizerInterface
{
    public function __construct(
        private readonly ErpStockClientInterface $erpStockClient,
        private readonly BatchProcessor $batchProcessor,
        private readonly ProductSkuValidatorInterface $productSkuValidator,
        private readonly StockBatchUpdaterInterface $stockBatchUpdater,
        private readonly Config $config
    ) {
    }

    public function execute(): SyncResult
    {
        $result = new SyncResult();

        $stockItems = $this->erpStockClient
            ->getStockItems();

        $batches = $this->batchProcessor
            ->createBatches(
                $stockItems,
                $this->config->getBatchSize()
            );

        foreach ($batches as $batch) {
            $this->processBatch(
                $batch,
                $result
            );

            $result->addProcessedItems(
                count($batch)
            );

            $result->incrementProcessedBatches();
        }

        return $result;
    }

    private function processBatch(
        array $batch,
        SyncResult $result
    ): void {
        $skus = [];

        foreach ($batch as $stockItem) {
            $skus[] = $stockItem->getSku();
        }

        $existingSkus = $this
            ->productSkuValidator
            ->getExistingSkus($skus);

        $existingSkuLookup = array_fill_keys(
            $existingSkus,
            true
        );

        $validStockItems = [];

        foreach ($batch as $stockItem) {
            if (
                isset(
                    $existingSkuLookup[
                        $stockItem->getSku()
                    ]
                )
            ) {
                $validStockItems[] = $stockItem;

                continue;
            }

            $result->addInvalidItems(1);
        }

        $result->addValidItems(
            count($validStockItems)
        );

        $updatedItems = $this
            ->stockBatchUpdater
            ->execute($validStockItems);

        $result->addUpdatedItems(
            $updatedItems
        );
    }
}
