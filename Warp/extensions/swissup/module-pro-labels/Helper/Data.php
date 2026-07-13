<?php

namespace Swissup\ProLabels\Helper;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Store\Model\StoreManagerInterface;

class Data
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var ProductLabels
     */
    private $systemLabels;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $memo = [];

    /**
     * Constructor
     *
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ProductLabels               $systemLabels
     * @param StoreManagerInterface       $storeManager
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        ProductLabels $systemLabels,
        StoreManagerInterface $storeManager

    ) {
        $this->categoryRepository = $categoryRepository;
        $this->systemLabels = $systemLabels;
        $this->storeManager = $storeManager;
    }

    /**
     * @return ProductLabels
     */
    public function getSystemLabelsInstance()
    {
        return $this->systemLabels;
    }

    /**
     * @param  Product $configurableProduct
     * @param  string $valueType       'PercentValue'|'AmountValue'
     * @return float
     */
    private function getConfigurableProductDiscountValue(
        Product $configurableProduct,
        $valueType
    ) {
        /** @var array $simpleProducts */
        $simpleProducts = $configurableProduct
            ->getTypeInstance()
            ->getUsedProducts($configurableProduct);
        $discountAmount = array_map([$this, "getDiscount{$valueType}"], $simpleProducts);

        return max($discountAmount);
    }

    /**
     * #discount_percent# placeholder
     *
     * @param  Product $product
     * @return float
     */
    public function getDiscountPercentValue(Product $product)
    {
        $key = "{$product->getId()}::{$product->getStoreId()}::discount_percent";
        if (!isset($this->memo[$key])) {
            $this->memo[$key] = $this->prepareDiscountPercentValue($product);
        }

        return $this->memo[$key];
    }

    /**
     * @param  Product $product
     * @return float
     */
    private function prepareDiscountPercentValue(Product $product)
    {
        if ('grouped' === $product->getTypeId()) {
            $cheapest = $product->getData('cheapest_product') ?:
                $this->systemLabels->getCheapestFromGrouped($product);
            $discountValue = $cheapest ? $this->getDiscountPercentValue($cheapest) : 0;
        } elseif ('bundle' === $product->getTypeId()) {
            $final = $product->getPriceInfo()->getPrice('final_price');
            $regular = $product->getPriceInfo()->getPrice('regular_price');
            $finalPrice = $final->getMinimalPrice()->getValue();
            $regularPrice = $regular->getMinimalPrice()->getValue();
            $discountValue = $this->calculateDiscountPercent($regularPrice, $finalPrice);
        } else {
            $finalPrice = $this->getFinalPriceValue($product);
            $regularPrice = $this->getPriceValue($product);
            $discountValue = $this->calculateDiscountPercent($regularPrice, $finalPrice);

            if (!$discountValue && 'configurable' === $product->getTypeId()) {
                // No discount found on superProduct level. Check child products
                $discountValue = $this->getConfigurableProductDiscountValue(
                    $product,
                    'PercentValue'
                );
            }
        }

        return $discountValue;
    }

    /**
     * @param  float $regulatPrice
     * @param  float $finalPrice
     * @return float
     */
    private function calculateDiscountPercent($regularPrice, $finalPrice)
    {
        $discountPercent = $regularPrice ?
            ((1 - $finalPrice / $regularPrice) * 100) :
            0;

        return $discountPercent;
    }

    /**
     * #discount_amount# placeholder
     *
     * @param  Product $product
     * @return float
     */
    public function getDiscountAmountValue(Product $product)
    {
        $key = "{$product->getId()}::{$product->getStoreId()}::discount_amount";
        if (!isset($this->memo[$key])) {
            $this->memo[$key] = $this->prepareDiscountAmountValue($product);
        }

        return $this->memo[$key];
    }

    /**
     * @param  Product $product
     * @return float
     */
    private function prepareDiscountAmountValue(Product $product)
    {
        if ('grouped' === $product->getTypeId()) {
            $cheapest = $product->getData('cheapest_product') ?:
                $this->systemLabels->getCheapestFromGrouped($product);
            $discountValue = $cheapest ? $this->getDiscountAmountValue($cheapest) : 0;
        } elseif ('bundle' === $product->getTypeId()) {
            $finalPrice = $product->getPriceInfo()->getPrice('final_price');
            $regularPrice = $product->getPriceInfo()->getPrice('regular_price');
            $discountValue = $regularPrice->getMinimalPrice()->getValue()
                - $finalPrice->getMinimalPrice()->getValue();
        } else {
            $finalPrice = $this->getFinalPriceValue($product);
            $regularPrice = $this->getPriceValue($product);
            $discountValue = $regularPrice > $finalPrice ?
                $regularPrice - $finalPrice :
                0;

            if (!$discountValue && 'configurable' === $product->getTypeId()) {
                // No discount found on superProduct level. Check child products
                $discountValue = $this->getConfigurableProductDiscountValue(
                    $product,
                    'AmountValue'
                );
            }
        }

        return $discountValue;
    }

    /**
     * #special_price# placeholder
     *
     * @param  Product $product
     * @return float
     */
    public function getSpecialPriceValue(Product $product)
    {
        if ('grouped' === $product->getTypeId()) {
            $cheapest = $product->getData('cheapest_product') ?:
                $this->systemLabels->getCheapestFromGrouped($product);

            return $cheapest ? $this->getSpecialPriceValue($cheapest) : 0;
        }

        return $this->systemLabels->getSpecialPrice($product);;
    }

    /**
     * #price# placeholder
     *
     * @param  Product $product
     * @return float
     */
    public function getPriceValue(Product $product)
    {
        if ('grouped' === $product->getTypeId()) {
            $cheapest = $product->getData('cheapest_product') ?:
                $this->systemLabels->getCheapestFromGrouped($product);

            return $cheapest ? $this->getPriceValue($cheapest) : 0;
        }

        return $this->systemLabels->getRegularPrice($product);
    }

    /**
     * #final_price# placeholder
     *
     * @param  Product $product
     * @return float
     */
    public function getFinalPriceValue(Product $product)
    {
        if ('grouped' === $product->getTypeId()) {
            $cheapest = $product->getData('cheapest_product') ?:
                $this->systemLabels->getCheapestFromGrouped($product);

            return $cheapest ? $this->getFinalPriceValue($cheapest) : 0;
        }

        return $this->systemLabels->getFinalPrice($product);
    }

    /**
     * #stock_item# placeholder
     *
     * @param  Product $product
     * @return float|string
     */
    public function getStockItemValue(Product $product)
    {
        return $this->systemLabels->getStockQty($product);
    }

    /**
     * Check if category is not root
     *
     * @param  \Magento\Catalog\Api\Data\CategoryInterface $category
     * @return boolean
     */
    private function isNotRoot($categoryId)
    {
        $store = $this->storeManager->getStore();
        return $categoryId != $store->getRootCategoryId();
    }

    /**
     * #category_name# placeholder
     *
     * @param  Product $product
     * @return string
     */
    public function getCategoryNameValue(Product $product)
    {
        $category = $product->getCategory();
        $categoryIds = array_filter($product->getCategoryIds(), [$this, 'isNotRoot']);
        $category = $this->categoryRepository->get(reset($categoryIds));

        return $category ? $category->getName() : '';
    }

    /**
     * #category_url# placeholder
     *
     * @param  Product $product
     * @return string
     */
    public function getCategoryUrlValue(Product $product)
    {
        $category = $product->getCategory();
        $categoryIds = array_filter($product->getCategoryIds(), [$this, 'isNotRoot']);
        $category = $this->categoryRepository->get(reset($categoryIds));

        return $category ? $category->getUrl() : '';
    }
}
