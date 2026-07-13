<?php

namespace Swissup\SeoCrossLinks\Model\Link\Source;

use Swissup\SeoCrossLinks\Model\Link;

class IsActive implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var Link
     */
    private $link;

    public function __construct(Link $link)
    {
        $this->link = $link;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->link->getAvailableStatuses() as $value => $label) {
            $result[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $result;
    }
}
