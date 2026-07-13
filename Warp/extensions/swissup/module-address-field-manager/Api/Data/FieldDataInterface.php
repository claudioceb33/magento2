<?php
namespace Swissup\AddressFieldManager\Api\Data;

interface FieldDataInterface
{
    /**
     * Get address type
     *
     * @return string
     */
    public function getAddressType();

    /**
     * Get attribute code
     *
     * @return string
     */
    public function getAttributeCode();

    /**
     * Get field label
     *
     * @return string
     */
    public function getLabel();

    /**
     * Get value
     *
     * @return string|array
     */
    public function getValue();

    /**
     * Set address type
     *
     * @param string $type
     * @return string
     */
    public function setAddressType($type);

    /**
     * Set attribute code
     *
     * @param string $code
     * @return string
     */
    public function setAttributeCode($code);

    /**
     * Set field label
     *
     * @param string $label
     * @return string
     */
    public function setLabel($label);

    /**
     * Set value
     *
     * @param string|array $value
     * @return string
     */
    public function setValue($value);
}
