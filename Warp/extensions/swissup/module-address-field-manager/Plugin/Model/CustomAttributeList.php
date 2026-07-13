<?php
namespace Swissup\AddressFieldManager\Plugin\Model;

class CustomAttributeList
{
    /**
     * @var \Magento\Customer\Api\AddressMetadataInterface
     */
    protected $addressMetadata;

    /**
     * @var array
     */
    protected $attributes = null;

    /**
     * @param \Magento\Customer\Api\AddressMetadataInterface $addressMetadata
     */
    public function __construct(
        \Magento\Customer\Api\AddressMetadataInterface $addressMetadata
    ) {
        $this->addressMetadata = $addressMetadata;
    }

    /**
     * Retrieve list of address custom attributes
     *
     * @return array
     */
    public function afterGetAttributes($subject, $result)
    {
        if ($this->attributes === null) {
            $this->attributes = [];
            $customAttributes = $this->addressMetadata->getCustomAttributesMetadata(
                \Magento\Customer\Api\Data\AddressInterface::class
            );
            if (is_array($customAttributes)) {
                foreach ($customAttributes as $attribute) {
                    $this->attributes[$attribute->getAttributeCode()] = $attribute;
                }
            }
        }

        return $this->attributes;
    }
}
