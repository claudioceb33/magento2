<?php
namespace Swissup\AddressFieldManager\Observer;

class OrderAddressCollectionLoad implements \Magento\Framework\Event\ObserverInterface
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
        $collection = $observer->getEvent()->getOrderAddressCollection();
        $itemsIds = $collection->getAllIds();

        if (count($itemsIds)) {
            $items = $collection->getItems();
            $customItems = $this->orderAddressModel->loadByIds($itemsIds);
            $addresses = [];
            foreach ($items as $address) {
                $addresses[$address->getId()] = $address;
            }
            foreach ($customItems as $customItem) {
                $addresses[
                    $customItem[$this->orderAddressModel->getIdFieldName()]
                ]->addData($customItem);
            }
        }

        return $this;
    }
}
