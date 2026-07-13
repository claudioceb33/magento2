<?php

namespace Ecommerce66\MultilineDiscount\Block\Adminhtml\Order;

use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Helper\Admin;
use Ecommerce66\MultilineDiscount\Helper\Data;

class Totals extends \Magento\Sales\Block\Adminhtml\Totals
{
    public function __construct(
        Context        $context,
        Registry       $registry,
        Admin          $adminHelper,
        protected Data $dataHelper,
        array          $data = []
    )
    {
        parent::__construct($context, $registry, $adminHelper, $data);
    }

    /**
     * Initialize order totals array
     *
     * @return $this
     */
    protected function _initTotals()
    {
        parent::_initTotals();
        $order = $this->getSource();
        if ((double)$order->getDiscountAmount() != 0 && $this->dataHelper->getIsActive()) {

            $discountLabel = $order->getDiscountDescription() ? __('Discount (%1)', $order->getDiscountDescription()) : __('Discount');
            $shippingAddress = $order->getShippingAddress();
            $discountItems = $this->dataHelper->getDiscountItems($order->getAllVisibleItems(), $shippingAddress);
            $this->_totals['discount'] = new DataObject(
                [
                    'code' => 'discount',
                    'value' => $order->getDiscountAmount(),
                    'base_value' => $order->getBaseDiscountAmount(),
                    'label' => $discountLabel,
                    'segments' => $discountItems
                ]
            );
        }
        $this->_totals['paid'] = new DataObject(
            [
                'code' => 'paid',
                'strong' => true,
                'value' => $this->getSource()->getTotalPaid(),
                'base_value' => $this->getSource()->getBaseTotalPaid(),
                'label' => __('Total Paid'),
                'area' => 'footer',
            ]
        );
        $this->_totals['refunded'] = new DataObject(
            [
                'code' => 'refunded',
                'strong' => true,
                'value' => $this->getSource()->getTotalRefunded(),
                'base_value' => $this->getSource()->getBaseTotalRefunded(),
                'label' => __('Total Refunded'),
                'area' => 'footer',
            ]
        );
        $code = 'due';
        $label = 'Total Due';
        $value = $this->getSource()->getTotalDue();
        $baseValue = $this->getSource()->getBaseTotalDue();
        if ($this->getSource()->getTotalCanceled() > 0 && $this->getSource()->getBaseTotalCanceled() > 0) {
            $code = 'canceled';
            $label = 'Total Canceled';
            $value = $this->getSource()->getTotalCanceled();
            $baseValue = $this->getSource()->getBaseTotalCanceled();
        }
        $this->_totals[$code] = new DataObject(
            [
                'code' => 'due',
                'strong' => true,
                'value' => $value,
                'base_value' => $baseValue,
                'label' => __($label),
                'area' => 'footer',
            ]
        );
        return $this;
    }
}
