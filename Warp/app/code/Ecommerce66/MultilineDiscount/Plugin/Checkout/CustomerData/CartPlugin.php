<?php

namespace Ecommerce66\MultilineDiscount\Plugin\Checkout\CustomerData;

use Magento\Checkout\CustomerData\Cart;
use Ecommerce66\MultilineDiscount\Helper\Data;
use Magento\Checkout\Model\Session;

class CartPlugin
{
    public function __construct(
        protected Data $dataHelper,
        protected Session $checkoutSession
    ) {}

    public function afterGetSectionData(
        Cart $subject,
        array $result
    ) {
        if ($this->dataHelper->getIsActive()) {
            $appliedRules = [];
            $quote = $this->checkoutSession->getQuote();
            foreach ($quote->getAllVisibleItems() as $item) {
                if ($item->getExtensionAttributes() && $item->getExtensionAttributes()->getDiscounts()) {
                    $discounts = $item->getExtensionAttributes()->getDiscounts();

                    foreach ($discounts as $discount) {
                        $this->dataHelper->setAppliedRules($discount, $appliedRules);
                    }
                    continue;
                }

                if ($item->getOptionByCode('discounts')) {
                    $discounts = $this->dataHelper->jsDecode($item->getOptionByCode('discounts')->getData('value'));
                    foreach ($discounts as $discount) {
                        $this->dataHelper->setAppliedRulesItems($discount, $appliedRules);
                    }
                }
            }


            $address = $quote->getShippingAddress();
            $this->setAddressDiscounts($address, $appliedRules);

            $result['custom_discounts'] = $appliedRules;
        }
        return $result;
    }

    protected function setAddressDiscounts($address, &$appliedRules)
    {
        $discounts = $address->getCustomDiscounts() ? $this->dataHelper->jsDecode($address->getCustomDiscounts()) : ($this->checkoutSession->getCustomDiscounts() ? $this->dataHelper->jsDecode($this->checkoutSession->getCustomDiscounts()) : []);
        foreach ($discounts as $discount) {
            $this->dataHelper->setAppliedRulesItems($discount, $appliedRules);
        }
    }
}
