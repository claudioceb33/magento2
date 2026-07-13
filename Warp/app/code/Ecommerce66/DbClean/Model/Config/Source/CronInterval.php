<?php

namespace Ecommerce66\DbClean\Model\Config\Source;

use \Magento\Framework\Data\OptionSourceInterface;

class CronInterval implements OptionSourceInterface
{

    /**
     * @return array
     */
    public function getOptionArray()
    {
        return [
            "0 */6 * * *" => "6hs",
            "0 7,19 * * *" => "12hs",
            "0 1 * * *" => "24hs",
            "0 1 * * SUN" => "7dias"
        ];
    }

    /**
     * Get Grid row type array for option element.
     * @return array
     */
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }
}
