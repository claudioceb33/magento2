<?php
declare(strict_types=1);

namespace Ceb\ErpStockSync\Model\Erp;

use Ceb\ErpStockSync\Api\ErpStockClientInterface;
use Ceb\ErpStockSync\Model\Config;
use Ceb\ErpStockSync\Model\Erp\Data\StockItem;
use Generator;

class MockErpStockClient2 implements ErpStockClientInterface
{
    private const TOTAL_ITEMS = 1200;

    public function __construct(
        private readonly Config $config
    ) {
    }

    public function getStockItems(): Generator
    {
        $pageSize = $this->config->getErpPageSize();
        $page = 1;

        do {
            $items = $this->getPage(
                $page,
                $pageSize
            );

            foreach ($items as $item) {
                yield $item;
            }

            $page++;
        } while (count($items) === $pageSize);
    }

    /**
     * @return StockItem[]
     */
    private function getPage(
        int $page,
        int $pageSize
    ): array {
        $offset = ($page - 1) * $pageSize;

        if ($offset >= self::TOTAL_ITEMS) {
            return [];
        }

        $limit = min(
            $offset + $pageSize,
            self::TOTAL_ITEMS
        );

        $items = [];

        for ($index = $offset; $index < $limit; $index++) {
            $items[] = new StockItem(
                sprintf('MOCK-SKU-%05d', $index + 1),
                (float)(($index * 7) % 100)
            );
        }

        return $items;
    }
}