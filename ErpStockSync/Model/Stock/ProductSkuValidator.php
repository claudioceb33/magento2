<?php

declare(strict_types=1);

namespace Ceb\ErpStockSync\Model\Stock;

use Ceb\ErpStockSync\Api\ProductSkuValidatorInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class ProductSkuValidator implements ProductSkuValidatorInterface
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    public function getExistingSkus(array $skus): array
    {
        if ($skus === []) {
            return [];
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(
                'sku',
                $skus,
                'in'
            )
            ->setPageSize(count($skus))
            ->create();

        $products = $this->productRepository
            ->getList($searchCriteria)
            ->getItems();

        $existingSkus = [];

        foreach ($products as $product) {
            $existingSkus[] = $product->getSku();
        }

        return $existingSkus;
    }
}
