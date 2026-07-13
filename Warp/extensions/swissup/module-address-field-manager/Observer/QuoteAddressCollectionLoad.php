<?php
namespace Swissup\AddressFieldManager\Observer;

class QuoteAddressCollectionLoad implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Swissup\AddressFieldManager\Model\ResourceModel\Address\Quote
     */
    protected $quoteAddressModel;

    /**
     * @param \Swissup\AddressFieldManager\Model\ResourceModel\Address\QuoteFactory $quoteAddressFactory
     */
    public function __construct(
        \Swissup\AddressFieldManager\Model\ResourceModel\Address\QuoteFactory $quoteAddressFactory
    ) {
        $this->quoteAddressModel = $quoteAddressFactory->create();
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $collection = $observer->getEvent()->getQuoteAddressCollection();
        $itemsIds = $collection->getAllIds();

        if (count($itemsIds)) {
            $items = $collection->getItems();
            $customItems = $this->quoteAddressModel->loadByIds($itemsIds);
            $addresses = [];
            foreach ($items as $address) {
                $addresses[$address->getId()] = $address;
            }
            foreach ($customItems as $customItem) {
                $addresses[
                    $customItem[$this->quoteAddressModel->getIdFieldName()]
                ]->addData($customItem);
            }
        }

        return $this;
    }
}
