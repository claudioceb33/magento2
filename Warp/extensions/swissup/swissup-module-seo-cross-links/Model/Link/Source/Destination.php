<?php

namespace Swissup\SeoCrossLinks\Model\Link\Source;

use Swissup\SeoCrossLinks\Model\Link;

class Destination implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */

    private $destination;

    public function __construct(Link $destination)
    {
        $this->destination = $destination;
    }
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->destination->getDestination() as $value => $label) {
            $result[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $result;
    }
}
