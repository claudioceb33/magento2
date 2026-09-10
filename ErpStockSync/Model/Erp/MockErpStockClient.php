<?php

declare(strict_types=1);

namespace Ceb\ErpStockSync\Model\Erp;

use Ceb\ErpStockSync\Api\ErpStockClientInterface;
use Ceb\ErpStockSync\Model\Config;
use Ceb\ErpStockSync\Model\Erp\Data\StockItem;
use Generator;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class MockErpStockClient implements ErpStockClientInterface
{
    public function __construct(
        private readonly Config $config,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    public function getStockItems(): Generator
    {
        $page = 1;
        $pageSize = $this->config->getErpPageSize();

        do {
            $products = $this->getProductPage(
                $page,
                $pageSize
            );

            foreach ($products as $product) {
                $sku = $product->getSku();

                if (((int)$product->getId() % 25) === 0) {
                    $sku = sprintf(
                        'ERP-INVALID-%d',
                        $product->getId()
                    );
                }

                yield new StockItem(
                    $sku,
                    $this->generateQuantity(
                        $product->getId()
                    )
                );
            }

            $page++;
        } while (count($products) === $pageSize);
    }

    private function getProductPage(
        int $page,
        int $pageSize
    ): array {
        $searchCriteria = $this->searchCriteriaBuilder
            ->setCurrentPage($page)
            ->setPageSize($pageSize)
            ->create();

        return $this->productRepository
            ->getList($searchCriteria)
            ->getItems();
    }

    private function generateQuantity(
        int|string|null $productId
    ): float {
        return (float)(
            ((int)$productId * 7) % 100
        );
    }
}
