<?php

namespace Ecommerce66\MultilineDiscount\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session;

class OrderItemDiscountOptions implements ObserverInterface
{
    public function __construct(
        protected Session $checkoutSession
    ) {}

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getQuote();
        $order = $observer->getOrder();
        $quoteItems = [];

        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            $quoteItems[$quoteItem->getId()] = $quoteItem;
        }

        foreach ($order->getAllVisibleItems() as $orderItem) {
            $quoteItemId = $orderItem->getQuoteItemId();
            $quoteItem = $quoteItems[$quoteItemId];
            $discounts = $quoteItem->getOptionByCode('discounts');


            if ($discounts->getValue()) {
                $options = $orderItem->getProductOptions();
                $options['discounts'] = json_decode($discounts->getValue());
                $orderItem->setProductOptions($options);
            }
        }
        $quoteAddress = $quote->getShippingAddress();
        $orderAddress = $order->getShippingAddress();
        $extAtts = $quoteAddress->getExtensionAttributes();
        $discounts = $extAtts && $extAtts->getCustomDiscounts() ? $extAtts->getCustomDiscounts() : $this->checkoutSession->getCustomDiscounts();
        if ($discounts) {
            $orderAddress->setCustomDiscounts($discounts);
        }
        $this->checkoutSession->unsCustomDiscounts();
    }
}
