<?php

namespace Swissup\SeoCrossLinks\Model\Link\Source;

use Swissup\SeoCrossLinks\Model\Link;

class Type implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    private $link;

    public function __construct(Link $link)
    {
        $this->link = $link;
    }
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->link->getSearchIn() as $value => $label) {
            $result[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $result;
    }
}