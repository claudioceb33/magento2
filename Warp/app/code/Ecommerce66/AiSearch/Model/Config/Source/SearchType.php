<?php
declare(strict_types=1);

namespace Ecommerce66\AiSearch\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class SearchType implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'magento',  'label' => __('Magento')],
            ['value' => 'fallback', 'label' => __('Fallback ai')],
            ['value' => 'hybrid',   'label' => __('Hybrid')],
            ['value' => 'ai',       'label' => __('AI only')],
        ];
    }
}
