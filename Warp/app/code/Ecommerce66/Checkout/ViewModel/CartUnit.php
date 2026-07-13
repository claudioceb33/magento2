<?php

namespace Ecommerce66\Checkout\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Catalog\Helper\Data as TaxHelper;

class CartUnit implements ArgumentInterface
{
    /**
     * @var TimezoneInterface
     */
    protected $timezoneInterface;

    /**
     * @var TaxHelper
     */
    protected $taxHelper;

    public function __construct(
        TimezoneInterface $timezoneInterface,
        TaxHelper $taxHelper
    ) {
        $this->timezoneInterface = $timezoneInterface;
        $this->taxHelper = $taxHelper;
    }

    public function getValidSpecialPrice($item)
    {
        $product = $item->getProduct();
        if ($product->getTypeId()=='configurable') {
            $product = $item->getOptionByCode('simple_product')->getProduct();
        }
        $specialPrice = $product->getSpecialPrice();
        if (!$specialPrice) return false;

        $price = $this->taxHelper->getTaxPrice($product, $product->getPrice(), true);

        $today = $this->timezoneInterface->date()->format('Y-m-d').' 00:00:00';
        $fromDate = $product->getSpecialFromDate();
        $toDate = $product->getSpecialToDate();

        if (($fromDate == null || $today >= $fromDate) && ($toDate == null || $today <= $toDate)) {
            return $price;
        }
        return false;
    }
}
