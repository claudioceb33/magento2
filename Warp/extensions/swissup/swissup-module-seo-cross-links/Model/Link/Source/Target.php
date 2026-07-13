<?php

namespace Swissup\SeoCrossLinks\Model\Link\Source;

use Swissup\SeoCrossLinks\Model\Link;

class Target implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */

    private $target_attr;

    public function __construct(Link $target_attr)
    {
        $this->target_attr = $target_attr;
    }
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->target_attr->getTargetAttr() as $value => $label) {
            $result[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $result;
    }
}
