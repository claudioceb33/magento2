<?php
namespace Swissup\FieldManager\Model\Adminhtml\System\Config\Source;

class Inputtype extends \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype
{
    /**
     * List of supported input types
     */
    const ALLOWED_TYPES = [
        'text', 'textarea', 'date', 'boolean', 'multiselect', 'select'
    ];

    /**
     * Return array of options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionsArray = parent::toOptionArray();

        $allowedOptions = array_filter($optionsArray, function ($var) {
            return in_array($var['value'], self::ALLOWED_TYPES);
        });

        // Change 'Dropdown' label to 'Single Select' because
        // we offer to choose between Dropdown or Radio buttons display types
        $allowedOptions = array_map(function ($item) {
            if ($item['value'] === 'select') {
                $item['label'] = __('Single Select');
            }
            return $item;
        }, $allowedOptions);

        return $allowedOptions;
    }
}
