<?php
namespace Swissup\AddressFieldManager\Plugin\Controller;

class OrderAddressSave
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

    /**
     * Save order address
     */
    public function beforeExecute(
        \Magento\Sales\Controller\Adminhtml\Order\AddressSave $subject
    ) {
        $addressId = $subject->getRequest()->getParam('address_id');
        $data = $subject->getRequest()->getPostValue();

        $customData = [];
        $codes = $this->helper->getCustomAttributeCodes();
        foreach ($codes as $code) {
            if (isset($data[$code])) {
                $value = $data[$code];
                $customData[$code] = is_array($value) ? implode(',', $value) : $value;
            }
        }

        $this->orderAddressModel
            ->addData($customData)
            ->setId($addressId)
            ->save();
    }
}
