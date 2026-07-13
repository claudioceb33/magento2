<?php
namespace Swissup\AddressFieldManager\Plugin\Model;

/**
 * @deprecated disabled in etc/di.xml
 * @see https://github.com/swissup/module-address-field-manager/issues/19
 */
class DefaultCheckoutConfigProvider
{
    /**
     * @var \Magento\Customer\Model\AttributeMetadataDataProvider
     */
    private $attributeMetadata;

    /**
     * @param \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadata
     */
    public function __construct(
        \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadata
    ) {
        $this->attributeMetadata = $attributeMetadata;
    }

    /**
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param array $result
     * @return array $result
     */
    public function afterGetConfig(
        \Magento\Checkout\Model\DefaultConfigProvider $subject,
        $result
    ) {
        if (!isset($result['customerData']) || !isset($result['customerData']['addresses'])) {
            return $result;
        }

        $addresses = $result['customerData']['addresses'];
        foreach ($addresses as $key => $address) {
            if (isset($address['custom_attributes'])) {
                $customAttributes = $address['custom_attributes'];
                foreach ($customAttributes as $attribute) {
                    $attributeCode = $attribute['attribute_code'];
                    $optionText = $this->getOptionText($attributeCode, $attribute['value']);
                    $result['customerData']['addresses'][$key]
                        ['custom_attributes'][$attributeCode]['value'] = $optionText;
                }
            }
        }

        return $result;
    }

    /**
     * Get dropdown option text by id
     * @param  string $attributeCode
     * @param  string|int $attributeValue
     * @return string
     */
    private function getOptionText($attributeCode, $attributeValue)
    {
        $attribute = $this->attributeMetadata->getAttribute(
            \Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
            $attributeCode
        );

        if ($attribute->usesSource() &&
            $optionText = $attribute->getSource()->getOptionText($attributeValue)
        ) {
            return $optionText;
        }

        return $attributeValue;
    }
}
