<?php

namespace Ceb\OrderChangeState\Plugin\Sales\Model\ResourceModel;

use Magento\Sales\Model\ResourceModel\Order\Handler\State;
use Magento\Sales\Model\Order;

class OrderHandlerStatePlugin
{
    const STATE_PAYMENT_REVIEW = 'payment_review';
    const STATUS_PAYMENT_REVIEW = 'payment_review';

    /**
     * Plugin method to intersect the status change, whenever a order with virtual products (that cannot be changed) is set to complete instead of processing.
     *
     * @param State $subject
     * @param callable $proceed
     * @param Order $order
     * @return State
     */
    public function aroundCheck(State $subject, callable $proceed, Order $order)
    {
        $currentState = $order->getState();
        $currentStatus = $order->getStatus();

        $statusSet = $isGiftCardVirtual = false;

        foreach ($order->getAllVisibleItems() as $item) {
            if ($item->getProductType() == 'amgiftcard' && $item->getIsVirtual() == 1)
                $isGiftCardVirtual = true;
        }

        if ($isGiftCardVirtual) {
            $statusSet = $this->replaceStatusChangeForVirtualOrders($order, $currentState);
        }

        if (!$statusSet) {
            $proceed($order);
        }

        $this->commentStatusChange($currentStatus, $order);

        return $subject;
    }

    /**
     * In case when order cannot be shipped (because items are all virtual products), set status and state to configured values in ConfigHelper
     *
     * @param Order $order
     * @param $currentState
     * @return bool
     */
    private function replaceStatusChangeForVirtualOrders(Order $order, $currentState)
    {
        if ($currentState == Order::STATE_COMPLETE) {

            $newState = self::STATE_PAYMENT_REVIEW;
            $newStatus = self::STATUS_PAYMENT_REVIEW;

            $order->setState($newState)
                ->setStatus($newStatus);

            return true;
        }

        // in all other cases, proceed with the normal procedure.
        return false;
    }

    /**
     * @param $oldState
     * @param $oldStatus
     * @param Order $order
     */
    private function commentStatusChange($oldStatus, Order $order)
    {
        $newStatus = $order->getStatus();

        if ($oldStatus != $newStatus) {
            $order->addCommentToStatusHistory(
                __("Update of Order-Status: %1 > %2", $this->getStatusLabel($order, $oldStatus), $this->getStatusLabel($order, $newStatus)));
        }
    }

    /**
     * @param Order $order
     * @param $statusCode
     * @return string|null
     */
    private function getStatusLabel(Order $order, $statusCode)
    {
        try {
            return $order->getConfig()->getStatusLabel($statusCode);
        } catch (\Exception $e) {
            return $statusCode;
        }
        return $statusCode;
    }

}
