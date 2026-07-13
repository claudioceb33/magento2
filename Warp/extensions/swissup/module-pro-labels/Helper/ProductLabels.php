<?php

namespace Swissup\ProLabels\Helper;

use Swissup\ProLabels\Helper\AbstractLabel;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * System Labels - on sale, new, in stock, out stock
 */
class ProductLabels extends AbstractLabel
{
    /**
     * @return Get On Sale Label Data
     */
    public function getOnSaleLabel($product, $mode)
    {
        $config = $this->scopeConfig->getValue('prolabels/on_sale', ScopeInterface::SCOPE_STORE);
        $onSaleConfig = $config[$mode] ?? [];
        if (!$onSaleConfig["active"]
            || !$this->isOnSale($product)
        ) {
            return false;
        }

        $onSaleConfig['sort_order'] = $config['sort_order'] ?? '';

        return $this->getLabelOutputObject($onSaleConfig);
    }

    /**
     * @return Get Is New Label Data
     */
    public function getIsNewLabel($product, $mode)
    {
        $config = $this->scopeConfig->getValue('prolabels/is_new', ScopeInterface::SCOPE_STORE);
        $isNewConfig = $config[$mode] ?? [];
        if (!$isNewConfig["active"]
            || !$this->isNew($product)
        ) {
            return false;
        }

        $isNewConfig['sort_order'] = $config['sort_order'] ?? '';

        return $this->getLabelOutputObject($isNewConfig);
    }

    /**
     * @return Get Stock Label Data
     */
    public function getStockLabel($product, $mode)
    {
        $config = $this->scopeConfig->getValue('prolabels/in_stock', ScopeInterface::SCOPE_STORE);
        $stockConfig = $config[$mode] ?? [];
        if (!$stockConfig["active"]) {
            return false;
        }

        $qty = $this->stock->getQty($product);
        $isInStock = $this->stock->isInStock($product);
        if ($qty > 0
            && $qty < (float)$stockConfig['stock_lower']
            && $isInStock
        ) {
            $stockConfig['sort_order'] = $config['sort_order'] ?? '';

            return $this->getLabelOutputObject($stockConfig);
        }

        return false;
    }

    /**
     * @return Get Out Of Stock Label Data
     */
    public function getOutOfStockLabel($product, $mode)
    {
        $config = $this->scopeConfig->getValue('prolabels/out_stock', ScopeInterface::SCOPE_STORE);
        $stockConfig = $config[$mode] ?? [];
        if (!$stockConfig["active"]
            || !$this->stock->isManageStock($product)
        ) {
            return false;
        }

        $qty = $this->stock->getQty($product, 'max');
        $isOutOfStock = $this->stock->isOutOfStock($product);
        $isBackorders = $this->stock->getIsBackorders($product);
        if (($isOutOfStock || $qty <= 0)
            && !$isBackorders
        ) {
            $stockConfig['sort_order'] = $config['sort_order'] ?? '';

            return $this->getLabelOutputObject($stockConfig);
        }

        return false;
    }

    /**
     * Check If Product Has Discount
     *
     * @param $product \Magento\Catalog\Model\Product
     * @return
     */
    public function isOnSale($product)
    {
        if ('bundle' === $product->getTypeId()) {
            $finalPrice = $product->getPriceInfo()->getPrice('final_price');
            $regularPrice = $product->getPriceInfo()->getPrice('regular_price');
            return $finalPrice->getMinimalPrice()->getValue() < $regularPrice->getMinimalPrice()->getValue();
        } elseif ('grouped' === $product->getTypeId()) {
            $cheapest = $this->getCheapestFromGrouped($product);
            $product->setData('cheapest_product', $cheapest);
            if ($cheapest) {
                $finalPrice = $this->getFinalPrice($cheapest);
                $regularPrice = $this->getRegularPrice($cheapest);
                return $finalPrice < $regularPrice;
            }
        } else {
            $finalPrice = $this->getFinalPrice($product);
            $regularPrice = $this->getRegularPrice($product);
            $hasDiscount = $finalPrice < $regularPrice;
            if ($hasDiscount) {
                return true;
            }

            if ('configurable' === $product->getTypeId()) {
                // No discount found on super product level. Check child products.
                $items = $product->getTypeInstance()->getUsedProducts($product);
                foreach ($items as $item) {
                    $hasDiscount = $this->isOnSale($item);
                    if ($hasDiscount) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Improved method to get regular price of product
     *
     * @param  ProductInterface $product
     * @return float
     */
    public function getRegularPrice(ProductInterface $product)
    {
        return $this->price->getRegularPrice($product);
    }

    /**
     * Get final price of product
     *
     * @param  ProductInterface $product
     * @return float
     */
    public function getFinalPrice(ProductInterface $product)
    {
        return $this->price->getFinalPrice($product);
    }

    /**
     * Get special price of product
     *
     * @param  ProductInterface $product
     * @return float
     */
    public function getSpecialPrice(ProductInterface $product)
    {
        return $this->price->getSpecialPrice($product);
    }

    /**
     * Find cheapest product among products associated with grouped
     *
     * @param  ProductInterface $grouped
     * @return ProductInterface
     */
    public function getCheapestFromGrouped(ProductInterface $grouped)
    {
        if ('grouped' === $grouped->getTypeId()) {
            /** @var array $associatedProducts */
            $associatedProducts = $grouped->getTypeInstance()->getAssociatedProducts($grouped);
            $cheapest = reset($associatedProducts);
            $cheapest = array_reduce($associatedProducts, function ($cheapest, $product) {
                if ($this->getFinalPrice($product) < $this->getFinalPrice($cheapest)) {
                    $cheapest = $product;
                }

                return $cheapest;
            }, $cheapest);

            return $cheapest;
        }

        return null;
    }
}
