<?php

declare(strict_types=1);

namespace Ceb\ErpStockSync\Model\Stock;

use Ceb\ErpStockSync\Model\Config;
use Ceb\ErpStockSync\Model\Erp\Data\StockItem;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;

class SourceItemMapper
{
    public function __construct(
        private readonly SourceItemInterfaceFactory $sourceItemFactory,
        private readonly Config $config
    ) {
    }

    public function map(StockItem $erpStockItem): SourceItemInterface
    {
        $quantity = max(
            0.0,
            $erpStockItem->getQuantity()
        );

        /** @var SourceItemInterface $sourceItem */
        $sourceItem = $this->sourceItemFactory->create();

        $sourceItem->setSourceCode(
            $this->config->getSourceCode()
        );

        $sourceItem->setSku(
            $erpStockItem->getSku()
        );

        $sourceItem->setQuantity(
            $quantity
        );

        $sourceItem->setStatus(
            $quantity > 0
                ? SourceItemInterface::STATUS_IN_STOCK
                : SourceItemInterface::STATUS_OUT_OF_STOCK
        );

        return $sourceItem;
    }
}
