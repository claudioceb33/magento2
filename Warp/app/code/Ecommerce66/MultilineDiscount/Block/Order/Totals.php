<?php

namespace Ecommerce66\MultilineDiscount\Block\Order;

use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Ecommerce66\MultilineDiscount\Helper\Data;

class Totals extends \Magento\Sales\Block\Order\Totals
{
    public function __construct(
        Context $context,
        Registry $registry,
        protected Data $dataHelper,
        array $data = [])
    {
        parent::__construct($context, $registry, $data);
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
        return $this;
    }
}
