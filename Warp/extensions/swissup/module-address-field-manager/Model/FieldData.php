<?php
namespace Swissup\AddressFieldManager\Model;

class FieldData implements \Swissup\AddressFieldManager\Api\Data\FieldDataInterface
{
    /**
     * @var string
     */
    private $addressType;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string|array
     */
    private $value;

    /**
     * @inheritdoc
     */
    public function getAddressType()
    {
        return $this->addressType;
    }

    /**
     * @inheritdoc
     */
    public function getAttributeCode()
    {
        return $this->code;
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function setAddressType($type)
    {
        $this->addressType = $type;
        return $type;
    }

    /**
     * @inheritdoc
     */
    public function setAttributeCode($code)
    {
        $this->code = $code;
        return $code;
    }

    /**
     * @inheritdoc
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $label;
    }

    /**
     * @inheritdoc
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $value;
    }
}
