<?php
namespace Swissup\AddressFieldManager\Observer;

class OrderAddressSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Swissup\AddressFieldManager\Model\Address\Order
     */
    protected $orderAddressModel;

    /**
     * @var \Swissup\FieldManager\Helper\Data
     */
    protected $helper;

    /**
     * @param \Swissup\AddressFieldManager\Model\Address\OrderFactory $orderAddressFactory
     * @param \Swissup\FieldManager\Helper\Data $helper
     */
    public function __construct(
        \Swissup\AddressFieldManager\Model\Address\OrderFactory $orderAddressFactory,
        \Swissup\FieldManager\Helper\Data $helper
    ) {
        $this->orderAddressModel = $orderAddressFactory->create();
        $this->helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $address = $observer->getEvent()->getAddress();
        if ($data = $this->helper->fixAttributesValues($address)) {
            $this->orderAddressModel
                ->addData($data)
                ->setId($address->getId())
                ->save();
        }

        return $this;
    }
}
