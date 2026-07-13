<?php

namespace Swissup\RichSnippets\Model\Config\Source;

class StructuredDataFormat implements \Magento\Framework\Option\ArrayInterface
{

    const NO_STRUCTURED_DATA = 'no-data';

    const MICRODATA = 'microdata';

    const JSON_LD = 'json-ld';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::NO_STRUCTURED_DATA,
                'label' => __('No structured data')
            ],
            [
                'value' => self::MICRODATA,
                'label' => __('Microdata (implemented by Magento)')
            ],
            [
                'value' => self::JSON_LD,
                'label' => __('JSON-LD')
            ]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach ($this->toOptionArray() as $option) {
            $array[$option['value']] = $option['label'];
        }

        return $array;
    }
}
