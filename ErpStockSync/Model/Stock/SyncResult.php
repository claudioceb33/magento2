<?php
declare(strict_types=1);

namespace Ceb\ErpStockSync\Model\Stock;

final class SyncResult
{
    private int $processedItems = 0;

    private int $processedBatches = 0;

    private int $validItems = 0;

    private int $invalidItems = 0;

    private int $updatedItems = 0;

    public function addProcessedItems(int $amount): void
    {
        $this->processedItems += $amount;
    }

    public function addUpdatedItems(int $amount): void
    {
        $this->updatedItems += $amount;
    }

    public function incrementProcessedBatches(): void
    {
        $this->processedBatches++;
    }

    public function addValidItems(int $amount): void
    {
        $this->validItems += $amount;
    }

    public function addInvalidItems(int $amount): void
    {
        $this->invalidItems += $amount;
    }

    public function getProcessedItems(): int
    {
        return $this->processedItems;
    }

    public function getProcessedBatches(): int
    {
        return $this->processedBatches;
    }

    public function getValidItems(): int
    {
        return $this->validItems;
    }

    public function getInvalidItems(): int
    {
        return $this->invalidItems;
    }

    public function getUpdatedItems(): int
    {
        return $this->updatedItems;
    }
}