<?php

namespace Swissup\SeoPager\Model\Config\Source;

class StrategyNoViewAll extends Strategy
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();

        return array_filter($options, function ($item) {
            return $item['value'] !== self::REL_CANONICAL;
        });
    }
}
