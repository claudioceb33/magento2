<?php
namespace Swissup\AddressFieldManager\Plugin;

use Magento\Framework\DataObject;

class DataObjectCopy
{
    const ITEMS_TO_CHECK = [
        'sales_convert_quote_address_to_order_address',
        'sales_copy_order_billing_address_to_order',
        'sales_copy_order_shipping_address_to_order',
        'customer_address_to_quote_address',
        'sales_convert_quote_address_to_customer_address',
        'order_address_to_customer_address'
    ];

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
     * @param DataObject\Copy $subject
     * @param array|DataObject|null $result
     * @param string $fieldset
     * @param string $aspect
     * @param array|DataObject $source
     * @param array|DataObject $target
     * @param string $root
     * @return array|DataObject|null the value of $target
     */
    public function afterCopyFieldsetToTarget(
        DataObject\Copy $subject,
        $result,
        $fieldset,
        $aspect,
        $source,
        $target,
        $root = 'global'
    ) {
        if (!$source) {
            return $result;
        }

        if (in_array($fieldset . '_' . $aspect, self::ITEMS_TO_CHECK)) {
            if (is_array($source)) {
                $source = new DataObject($source);
            }
            
            $attributes = $this->helper->getCustomAttributeCodes();
            foreach ($attributes as $attribute) {
                if (is_array($result)) {
                    $result[$attribute] = $source->getData($attribute);
                } else {
                    $result->setData($attribute, $source->getData($attribute));
                }
            }
        }

        return $result;
    }
}
