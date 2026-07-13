<?php

namespace Ecommerce66\MultilineDiscount\Model\Quote;

use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Ecommerce66\MultilineDiscount\Helper\Data as DataHelper;
use Magento\Checkout\Model\Session;

class CustomDiscount extends AbstractTotal
{
    public const COLLECTOR_TYPE_CODE = 'discount';

    public function __construct(
        protected RuleRepositoryInterface $ruleRepository,
        protected DataHelper $dataHelper,
        protected Session $checkoutSession
    )
    {
        $this->setCode(self::COLLECTOR_TYPE_CODE);
    }

    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        return $this;
    }

    public function fetch(
        Quote $quote,
        Total $total
    ) {
        if ($this->dataHelper->getIsActive()) {
            $appliedRules = [];

            foreach ($quote->getAllVisibleItems() as $item) {
                $this->setItemsDiscounts($item, $quote, $appliedRules);

            }

            $address = $quote->getShippingAddress();
            $this->setAddressDiscounts($address, $appliedRules);

            if (!empty($appliedRules)) {
                $segments = [];
                foreach ($appliedRules as $k => $rule) {
                    $segments[] = [
                        'code' => $this->getCode() . '_' . $k,
                        'title' => $rule['label'],
                        'value' => $rule['amount'] * -1
                    ];
                }
                return [
                    'code' => $this->getCode(),
                    'title' => $this->getLabel(),
                    'value' => $segments
                ];
            }
        }
    }

    protected function setItemsDiscounts($item, $quote, &$appliedRules)
    {
        if ($item->getExtensionAttributes() && $item->getExtensionAttributes()->getDiscounts()) {
            $discounts = $item->getExtensionAttributes()->getDiscounts();
            $itemDiscounts = [];

            foreach ($discounts as $discount) {
                $itemDiscounts[] = [
                    'rule_id' => $discount->getRuleId(),
                    'rule_label' => $discount->getRuleLabel(),
                    'amount' => $discount->getDiscountData()->getAmount()
                ];
                $this->dataHelper->setAppliedRules($discount, $appliedRules);
            }
            $options = $item->getOptions(); // Get existing options
            $options[] = [
                'code' => 'discounts',
                'value' => $this->dataHelper->jsEncode($itemDiscounts),
                'product_id' => $item->getProductId(), // Required for some option types
            ];

            $item->setOptions($options);
            $item->save();
            $quote->save();
            return;
        }

        if ($item->getOptionByCode('discounts')) {
            $discounts = $this->dataHelper->jsDecode($item->getOptionByCode('discounts')->getData('value'));
            foreach ($discounts as $discount) {
                $this->dataHelper->setAppliedRulesItems($discount, $appliedRules);
            }
        }
    }

    protected function setAddressDiscounts($address, &$appliedRules)
    {
        $discounts = $address->getCustomDiscounts() ? $this->dataHelper->jsDecode($address->getCustomDiscounts()) : ($this->checkoutSession->getCustomDiscounts() ? $this->dataHelper->jsDecode($this->checkoutSession->getCustomDiscounts()) : []);
        foreach ($discounts as $discount) {
            $this->dataHelper->setAppliedRulesItems($discount, $appliedRules);
        }
    }
}
