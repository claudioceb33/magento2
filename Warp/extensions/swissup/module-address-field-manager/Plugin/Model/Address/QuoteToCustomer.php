<?php
namespace Swissup\AddressFieldManager\Plugin\Model\Address;

class QuoteToCustomer
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
     * @param \Magento\Quote\Model\Quote\Address $subject
     * @param \Magento\Customer\Api\Data\AddressInterface $result
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    public function afterExportCustomerAddress(
        \Magento\Quote\Model\Quote\Address $subject,
        \Magento\Customer\Api\Data\AddressInterface $result
    ) {
        $codes = $this->helper->getCustomAttributeCodes();
        foreach ($codes as $code) {
            $result->setCustomAttribute($code, $subject->getData($code));
        }

        return $result;
    }
}
