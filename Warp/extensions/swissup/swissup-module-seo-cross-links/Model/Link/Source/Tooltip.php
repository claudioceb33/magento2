<?php

namespace Swissup\SeoCrossLinks\Model\Link\Source;

use Swissup\SeoCrossLinks\Model\Link;

class Tooltip implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var tooltip
     */
    private $tooltip;

    public function __construct(Link $tooltip)
    {
        $this->tooltip = $tooltip;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->tooltip->showTooltip() as $value => $label) {
            $result[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $result;
    }
}
