<?php
namespace Swissup\AddressFieldManager\Plugin\Model\Address;

use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Api\Data\OrderAddressInterface;

class QuoteToOrder
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
     * @param \Magento\Quote\Model\Quote\Address\ToOrderAddress $subject
     * @param OrderAddressInterface $result
     * @param Address $object
     * @param array $data
     * @return OrderAddressInterface
     */
    public function afterConvert(
        \Magento\Quote\Model\Quote\Address\ToOrderAddress $subject,
        $result,
        Address $object,
        $data = []
    ) {
        $codes = $this->helper->getCustomAttributeCodes();
        $attributes = $this->helper->getEntityAttributes();
        foreach ($codes as $code) {
            $value = (string) $object->getData($code);

            // replace newline with comma for multiselect attributes
            if ($attributes[$code]->getFrontendInput() === 'multiselect') {
                $value = str_replace(PHP_EOL, ',', $value);
            }

            $result->setData($code, $value);
        }

        return $result;
    }
}
