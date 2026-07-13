<?php
namespace Swissup\AddressFieldManager\Observer;

class OrderAddressLoad implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Swissup\AddressFieldManager\Model\ResourceModel\Address\Order
     */
    protected $orderAddressModel;

    /**
     * @param \Swissup\AddressFieldManager\Model\ResourceModel\Address\OrderFactory $orderAddressFactory
     */
    public function __construct(
        \Swissup\AddressFieldManager\Model\ResourceModel\Address\OrderFactory $orderAddressFactory
    ) {
        $this->orderAddressModel = $orderAddressFactory->create();
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $address = $observer->getEvent()->getAddress();
        $customItems = $this->orderAddressModel->loadByIds([$address->getId()]);
        if (isset($customItems[0])) {
            $address->addData($customItems[0]);
        }

        return $this;
    }
}
