<?php

namespace Swissup\ProLabels\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Pricing\Price as ProductPrice;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Price
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param  ProductInterface $product
     * @return float
     */
    public function getFinalPrice(ProductInterface $product)
    {
        $code = ProductPrice\FinalPrice::PRICE_CODE;
        $finalPrice = $product->getPriceInfo()->getPrice($code);
        if ('bundle' === $product->getTypeId()) {
            return $finalPrice->getMinimalPrice()->getValue();
        }

        if ('configurable' === $product->getTypeId()) {
            // Inspired by \Magento\Catalog\Pricing\Price\RegularPrice
            // FIX. Configurable product dosn't apply convert and round.
            $priceInCurrentCurrency = $this->priceCurrency->convertAndRound(
                $finalPrice->getAmount()->getValue()
            );

            return $priceInCurrentCurrency ? (float)$priceInCurrentCurrency : 0;
        }

        return $finalPrice->getAmount()->getValue();
    }

    /**
     * @param  ProductInterface $product
     * @return float
     */
    public function getRegularPrice(ProductInterface $product)
    {
        $code = ProductPrice\RegularPrice::PRICE_CODE;
        $priceInfo = $product->getPriceInfo();
        $price = $priceInfo->getPrice($code)
            ->getAmount()
            ->getValue();;
        if ('configurable' === $product->getTypeId()) {
            // Inspired by \Magento\Catalog\Pricing\Price\RegularPrice
            // FIX. Configurable product dosn't apply convert and round.
            $priceInCurrentCurrency = $this->priceCurrency->convertAndRound($price);
            $price = $priceInCurrentCurrency ? (float)$priceInCurrentCurrency : 0;
        }

        return $price;
    }

    /**
     * @param  ProductInterface $product
     * @return float
     */
    public function getSpecialPrice(ProductInterface $product)
    {
        $code = ProductPrice\SpecialPrice::PRICE_CODE;
        $specialPrice = $product->getPriceInfo()->getPrice($code);
        return $specialPrice->getAmount()->getValue();
    }
}
