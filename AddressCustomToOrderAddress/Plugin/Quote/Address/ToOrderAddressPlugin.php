<?php

namespace Ceb\AddressCustomToOrderAddress\Plugin\Quote\Address;

/**
 * Class ToOrderAddressPlugin
 * @package Ceb\AddressCustomToOrderAddress\Quote\Model\Quote\Address
 */
class ToOrderAddressPlugin
{
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * ToOrderAddressPlugin constructor.
     * @param \Magento\Checkout\Model\Cart $cart
     */
    public function __construct(
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->cart = $cart;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\ToOrderAddress $subject
     * @param $interceptedOutput
     * @return mixed
     */
    public function afterConvert(
        \Magento\Quote\Model\Quote\Address\ToOrderAddress $subject,
        $interceptedOutput
    ) {
        if($interceptedOutput->getAddressType()=='shipping') {
            $shippingAddress = $this->cart->getQuote()->getShippingAddress();

            $interceptedOutput->setDni($shippingAddress->getDni());
        } else {
            $billingAddress = $this->cart->getQuote()->getBillingAddress();

            $interceptedOutput->setDni($billingAddress->getDni());
        }

        return $interceptedOutput;
    }
}