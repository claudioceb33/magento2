<?php

declare(strict_types=1);

namespace Ceb\ErpStockSync\Api;

use Ceb\ErpStockSync\Model\Erp\Data\StockItem;

interface StockBatchUpdaterInterface
{
    /**
     * @param StockItem[] $stockItems
     */
    public function execute(array $stockItems): int;
}
