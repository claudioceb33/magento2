<?php
declare(strict_types=1);

namespace Ceb\ErpStockSync\Api;

use Ceb\ErpStockSync\Model\Stock\SyncResult;

interface StockSynchronizerInterface
{
    public function execute(): SyncResult;
}