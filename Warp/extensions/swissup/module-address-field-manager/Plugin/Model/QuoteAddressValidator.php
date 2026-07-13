<?php

namespace Swissup\AddressFieldManager\Plugin\Model;

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;

class QuoteAddressValidator
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * @param \Magento\Quote\Model\QuoteAddressValidator $subject
     * @param void $result
     * @param CartInterface $cart
     * @param AddressInterface $address
     * @return void
     */
    public function afterValidateForCart(
        \Magento\Quote\Model\QuoteAddressValidator $subject,
        $result,
        CartInterface $cart,
        AddressInterface $address
    ) {
        // validate saved addresses because magento do this to late (before place order)
        if (!$address->getCustomerAddressId()) {
            return $result;
        }

        // validate requests to shipping-information and payment-information only.
        if (method_exists($this->request, 'getPathInfo')) {
            $pathInfo = $this->request->getPathInfo();

            if (strpos($pathInfo, '/shipping-information') === false &&
                strpos($pathInfo, '/payment-information') === false
            ) {
                return $result;
            }
        } else {
            return $result;
        }

        $validationErrors = [];
        $validationResult = $address->validate();

        if ($validationResult !== true) {
            // afm| - is the marker we search in js mixin.
            $validationErrors = ['afm|' . $address->getCustomerAddressId() . '|' . __('Please check address information.')];
        }

        if (is_array($validationResult)) {
            $validationErrors = array_merge($validationErrors, $validationResult);
        }

        if ($validationErrors) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(implode(' ', $validationErrors))
            );
        }

        return $result;
    }
}
