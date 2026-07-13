<?php

namespace Ecommerce66\Checkout\Plugin\Checkout\CustomerData;

use Magento\Checkout\CustomerData\DefaultItem;
use Magento\Quote\Model\Quote\Item;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Catalog\Helper\Data as TaxHelper;

class DefaultItemPlugin
{

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * @var TimezoneInterface
     */
    protected $timezoneInterface;

    /**
     * @var TaxHelper
     */
    protected $taxHelper;

    public function __construct(
        \Magento\Checkout\Helper\Data $checkoutHelper,
        TimezoneInterface $timezoneInterface,
        TaxHelper $taxHelper
    ) {
        $this->checkoutHelper = $checkoutHelper;
        $this->timezoneInterface = $timezoneInterface;
        $this->taxHelper = $taxHelper;
    }

    public function aroundGetItemData(DefaultItem $subject, \Closure $proceed, Item $item): array
    {
        $data = $proceed($item);
        $product = $item->getProduct();

        if ($product->getTypeId()=='configurable') {
            $product = $item->getOptionByCode('simple_product')->getProduct();
        }
        $price = $this->taxHelper->getTaxPrice($product, $product->getPrice(), true);
        $atts = [
            "regular_price" => $this->getValidSpecialPrice($product)?$this->checkoutHelper->formatPrice($price):false
        ];

        return array_merge($data, $atts);
    }

    protected function getValidSpecialPrice($product)
    {
        $specialPrice = $product->getSpecialPrice();
        if (!$specialPrice) return false;

        $today = $this->timezoneInterface->date()->format('Y-m-d').' 00:00:00';
        $fromDate = $product->getSpecialFromDate();
        $toDate = $product->getSpecialToDate();

        if (($fromDate == null || $today >= $fromDate) && ($toDate == null || $today <= $toDate)) {
            return $specialPrice;
        }
        return false;
    }
}
