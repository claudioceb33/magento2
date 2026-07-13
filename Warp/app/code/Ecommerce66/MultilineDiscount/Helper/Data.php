<?php

namespace Ecommerce66\MultilineDiscount\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\DataObject;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    protected const XML_ACTIVE_FIELD = 'multiline_discount/settings/';

    public function __construct(
        Context $context,
        protected JsonHelper $jsonHelper,
        protected StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
    }

    protected function getConfigValue($field) {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->scopeConfig->getValue(
            self::XML_ACTIVE_FIELD.$field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function jsEncode($data)
    {
        return $data ? $this->jsonHelper->jsonEncode($data):'';
    }

    public function jsDecode($data)
    {
        return $data ? $this->jsonHelper->jsonDecode($data):[];
    }

    public function setAppliedRules($discount, &$appliedRules)
    {
        if (!array_key_exists($discount->getRuleId(), $appliedRules)) {
            $appliedRules[$discount->getRuleId()] = [
                'label' => $discount->getRuleLabel(),
                'amount' => 0
            ];
        }
        $appliedRules[$discount->getRuleId()]['amount'] = $appliedRules[$discount->getRuleId()]['amount'] + $discount->getDiscountData()->getAmount();

    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function setAppliedRulesItems($discount, &$appliedRules, $dataObject = false)
    {
        if (!array_key_exists($discount['rule_id'], $appliedRules)) {
            $rule = [
                'label' => $discount['rule_label'],
                'amount' => 0
            ];
            $appliedRules[$discount['rule_id']] = $dataObject ? new DataObject($rule) : $rule;
        }

        if ($dataObject) {
            $appliedRules[$discount['rule_id']]->setValue($appliedRules[$discount['rule_id']]->getValue() + ($discount['amount']*-1));
            return;
        }
        $appliedRules[$discount['rule_id']]['amount'] = $appliedRules[$discount['rule_id']]['amount'] + $discount['amount'];

    }

    public function getDiscountItems($items, $address)
    {
        $appliedRules = [];
        foreach ($items as $item) {
            if ($item->getProductOptionByCode('discounts')) {
                $discounts = $item->getProductOptionByCode('discounts');
                foreach ($discounts as $discount) {
                    $this->setAppliedRulesItems($discount, $appliedRules, true);
                }
            }
        }
        if ($address->getCustomDiscounts()) {
            $discounts = $this->jsDecode($address->getCustomDiscounts());
            foreach ($discounts as $discount) {
                $this->setAppliedRulesItems($discount, $appliedRules, true);
            }
        }
        return new DataObject($appliedRules);
    }

    public function getIsActive()
    {
        return (bool)$this->getConfigValue('active');
    }
}
