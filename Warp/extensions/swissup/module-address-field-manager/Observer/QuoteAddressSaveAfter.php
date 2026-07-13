<?php
namespace Swissup\AddressFieldManager\Observer;

class QuoteAddressSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Swissup\AddressFieldManager\Model\Address\Quote
     */
    protected $quoteAddressModel;

    /**
     * @var \Swissup\FieldManager\Helper\Data
     */
    protected $helper;

    /**
     * @param \Swissup\AddressFieldManager\Model\Address\QuoteFactory $quoteAddressFactory
     * @param \Swissup\FieldManager\Helper\Data $helper
     */
    public function __construct(
        \Swissup\AddressFieldManager\Model\Address\QuoteFactory $quoteAddressFactory,
        \Swissup\FieldManager\Helper\Data $helper
    ) {
        $this->quoteAddressModel = $quoteAddressFactory->create();
        $this->helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $address = $observer->getEvent()->getQuoteAddress();
        if ($data = $this->helper->fixAttributesValues($address)) {
            $this->quoteAddressModel
                ->addData($data)
                ->setId($address->getId())
                ->save();
        }

        return $this;
    }
}
