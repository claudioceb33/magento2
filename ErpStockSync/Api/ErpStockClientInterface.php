<?php

declare(strict_types=1);

namespace Ceb\ErpStockSync\Api;

use Generator;

interface ErpStockClientInterface
{
    /**
     * @return Generator<int, \Ceb\ErpStockSync\Model\Erp\Data\StockItem>
     */
    public function getStockItems(): Generator;
}
