<?php
declare(strict_types=1);

namespace Ecommerce66\AiCore\Model\Config\Source\Cron;

use Magento\Framework\Data\OptionSourceInterface;

class CatalogDaily implements OptionSourceInterface
{
    /** 00–03 AM only, labels amigables; values = CRON expr */
    public function toOptionArray(): array
    {
        return [
            ['value' => '0 0 * * *',  'label' => __('Every day at 00:00')],
            ['value' => '0 1 * * *',  'label' => __('Every day at 01:00')],
            ['value' => '0 2 * * *',  'label' => __('Every day at 02:00')],
            ['value' => '0 3 * * *',  'label' => __('Every day at 03:00')],
        ];
    }
}
