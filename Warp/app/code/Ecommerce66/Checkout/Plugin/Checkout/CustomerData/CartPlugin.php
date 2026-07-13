<?php

namespace Ecommerce66\Checkout\Plugin\Checkout\CustomerData;

use Magento\Checkout\CustomerData\Cart;
use Magento\Checkout\Helper\Data;

class CartPlugin
{
    /**
     * @var Data
     */
    protected $checkoutHelper;

    public function __construct(
        Data $checkoutHelper
    ) {
        $this->checkoutHelper = $checkoutHelper;
    }

    public function afterGetSectionData(Cart $subject, array $result)
    {
        $quote = $this->checkoutHelper->getQuote();
        $ivaAmount = $this->getTaxTotal($quote);
        $result['iva_total'] = $ivaAmount;
        return $result;
    }

    protected function getTaxTotal($quote)
    {
        $total = 0;
        foreach ($quote->getAllVisibleItems() as $item) {
            $total += $item->getTaxAmount();
        }
        return $this->checkoutHelper->formatPrice($total);
    }
}
