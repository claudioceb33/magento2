<?php

declare(strict_types=1);

namespace Ceb\ErpStockSync\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Mode implements OptionSourceInterface
{
    private const MODE_MOCK = 'mock';
    private const MODE_HTTP = 'http';

    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::MODE_MOCK,
                'label' => __('Mock ERP'),
            ],
            [
                'value' => self::MODE_HTTP,
                'label' => __('HTTP ERP API'),
            ],
        ];
    }
}
