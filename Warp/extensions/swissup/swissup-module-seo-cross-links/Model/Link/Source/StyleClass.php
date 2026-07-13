<?php

namespace Swissup\SeoCrossLinks\Model\Link\Source;

use Swissup\SeoCrossLinks\Model\Link;

class StyleClass implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Option getter
     * @var array
     */
    private $class;

    public function __construct(Link $class)
    {
        $this->class = $class;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->class->getStyleClass() as $value => $label) {
            $result[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $result;
    }
}
