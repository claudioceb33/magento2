<?php

namespace Ecommerce66\MultilineDiscount\Plugin;

use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\Session;

class ShippingInformationManagementPlugin
{
    public function __construct(
        protected Session $checkoutSession
    ) {}
    /**
     *
     * @param ShippingInformationManagement $subject
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagement $subject,
                                      $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        $shippingAddress = $addressInformation->getShippingAddress();
        $extAttributes = $shippingAddress->getExtensionAttributes();
        $customDiscounts = ($extAttributes && $extAttributes->getCustomDiscounts()) ? $extAttributes->getCustomDiscounts():$this->checkoutSession->getCustomDiscounts();
        if ($customDiscounts) {
            $shippingAddress->setCustomDiscounts($customDiscounts);
        }
    }
}
