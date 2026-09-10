<?php

declare(strict_types=1);

namespace Ceb\ErpStockSync\Model\Stock;

use Ceb\ErpStockSync\Api\StockBatchUpdaterInterface;
use Ceb\ErpStockSync\Model\Config;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;

class StockBatchUpdater implements StockBatchUpdaterInterface
{
    public function __construct(
        private readonly SourceItemsSaveInterface $sourceItemsSave,
        private readonly SourceItemMapper $sourceItemMapper,
        private readonly Config $config
    ) {
    }

    public function execute(array $stockItems): int
    {
        if ($stockItems === []) {
            return 0;
        }

        if ($this->config->isDryRun()) {
            return 0;
        }

        $sourceItems = [];

        foreach ($stockItems as $stockItem) {
            $sourceItems[] = $this->sourceItemMapper->map(
                $stockItem
            );
        }

        $this->sourceItemsSave->execute(
            $sourceItems
        );

        return count($sourceItems);
    }
}
