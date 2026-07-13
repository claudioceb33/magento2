<?php
namespace Ecommerce66\Brands\Model\System\Attribute\Source;

use Magento\Framework\Data\OptionSourceInterface;

class LinkType implements OptionSourceInterface
{
    /**
     * @return array|array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'landing', 'label' => __('Link to brand index')],
            ['value' => 'category', 'label' => __('Link to category')]
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
            'landing' => __('Link to brand index'),
            'category' => __('Link to category')];
    }
}
