<?php

namespace Swissup\ProLabelsConfigurableProduct\Plugin\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Swissup\ProLabels\Model\Stock as Subject;

class Stock
{
    /**
     * Get simple products of configurable and preload stock data for them
     */
    private function getUsedProducts(
        ProductInterface $configurable,
        Subject $subject
    ): array {
        $usedProducts = $configurable
            ->getTypeInstance()
            ->getUsedProducts($configurable);
        $subject->preloadStockForProducts($usedProducts);

        return $usedProducts;
    }

    private function isAtLeastOneOfSimplesTrue(
        ProductInterface $configurable,
        Subject $subject,
        string $method
    ): bool {
        $usedProducts = $this->getUsedProducts($configurable, $subject);
        foreach ($usedProducts as $simpleProduct) {
            if ($subject->{$method}($simpleProduct)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Qty of configurable is qty among "In Stock" simples
     * calculated using aggregate function
     */
    public function afterGetQty(
        Subject $subject,
        float $result,
        ProductInterface $product,
        string $aggregateFunction = 'min'
    ): float {
        if ('configurable' !== $product->getTypeId()) {
            return $result;
        }

        $qty = [];
        $usedProducts = $this->getUsedProducts($product, $subject);
        foreach ($usedProducts as $simpleProduct) {
            if ($subject->isInStock($simpleProduct)) {
                $qty[] = $subject->getQty($simpleProduct);
            }
        }

        if (empty($qty)) {
            return 0;
        }

        switch ($aggregateFunction) {
            case 'min':
                return min($qty);
                break;

            case 'max':
                return max($qty);
                break;
        }

        return (float)reset($qty);
    }

    /**
     * Configurable is "In Stock" if at least one simple is in stock
     */
    public function afterIsInStock(
        Subject $subject,
        bool $result,
        ProductInterface $product
    ): bool {
        if ('configurable' !== $product->getTypeId()) {
            return $result;
        }

        if ($this->isAtLeastOneOfSimplesTrue($product, $subject, 'isInStock')) {
            return true;
        }

        return false;
    }

    /**
     * Configurable is Out of Stock when all simple are Out of Stock
     */
    public function afterIsOutOfStock(
        Subject $subject,
        bool $result,
        ProductInterface $product
    ): bool {
        if ('configurable' !== $product->getTypeId()) {
            return $result;
        }

        $usedProducts = $this->getUsedProducts($product, $subject);
        foreach ($usedProducts as $simpleProduct) {
            if (!$subject->isOutOfStock($simpleProduct)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Configurable has backorders if at least one simple has backorders
     */
    public function afterGetIsBackorders(
        Subject $subject,
        bool $result,
        ProductInterface $product
    ): bool {
        if ('configurable' !== $product->getTypeId()) {
            return $result;
        }

        if ($this->isAtLeastOneOfSimplesTrue($product, $subject, 'getIsBackorders')) {
            return true;
        }

        return false;
    }


    /**
     * Configurable has manage stock if at least one simple manages stock
     */
    public function afterIsManageStock(
        Subject $subject,
        bool $result,
        ProductInterface $product
    ): bool {
        if ('configurable' !== $product->getTypeId()) {
            return $result;
        }

        if ($this->isAtLeastOneOfSimplesTrue($product, $subject, 'isManageStock')) {
            return true;
        }

        return false;
    }

    /**
     * Append preload stock data with child products of configurable
     */
    public function beforePreloadStockForProducts(
        Subject $subject,
        array $products
    ): array {
        $childProducts = [];
        foreach ($products as $product) {
            if ('configurable' === $product->getTypeId()) {
                $childProducts = array_merge(
                    $childProducts,
                    $product->getTypeInstance()->getUsedProducts($product)
                );
            }
        }

        $products = array_merge(
            $products,
            $childProducts
        );

        return [$products];
    }
}
