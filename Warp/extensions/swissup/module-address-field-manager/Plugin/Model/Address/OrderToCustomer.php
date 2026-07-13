<?php
namespace Swissup\AddressFieldManager\Plugin\Model\Address;

class OrderToCustomer
{
    /**
     * @var \Swissup\FieldManager\Helper\Data
     */
    private $helper;

    /**
     * @param \Swissup\FieldManager\Helper\Data $helper
     */
    public function __construct(
        \Swissup\FieldManager\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Customer\Model\Address $subject
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    public function beforeUpdateData(
        \Magento\Customer\Model\Address $subject,
        \Magento\Customer\Api\Data\AddressInterface $address
    ) {
        $codes = $this->helper->getCustomAttributeCodes();
        $addressArr = $address->__toArray();
        foreach ($codes as $code) {
            if (isset($addressArr[$code])) {
                $address->setCustomAttribute($code, $addressArr[$code]);
            }
        }

        return [$address];
    }
}
