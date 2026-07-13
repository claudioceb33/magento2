<?php
declare(strict_types=1);

namespace Ecommerce66\AiCore\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class FeedFormat implements OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'csv',  'label' => __('CSV')],
            ['value' => 'json', 'label' => __('JSON')],
        ];
    }
}
