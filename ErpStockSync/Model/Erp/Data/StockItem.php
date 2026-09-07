<?php
declare(strict_types=1);

namespace Ceb\ErpStockSync\Model\Erp\Data;

final class StockItem
{
    public function __construct(
        private readonly string $sku,
        private readonly float $quantity
    ) {
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }
}