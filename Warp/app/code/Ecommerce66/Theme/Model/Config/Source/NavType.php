<?php
namespace Ecommerce66\Theme\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class NavType implements OptionSourceInterface
{
    /**
     * @return array|array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'default', 'label' => __('Native navigation')],
            ['value' => 'horizontal', 'label' => __('Horizontally expanded')],
            ['value' => 'vertical', 'label' => __('Vertically expanded')],
            ['value' => 'button', 'label' => __('Vertical button')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'default' => __('Native navigation'),
            'horizontal' => __('Horizontally expanded'),
            'vertical' => __('Vertically expanded'),
            'button' => __('Vertical button')
        ];
    }
}
