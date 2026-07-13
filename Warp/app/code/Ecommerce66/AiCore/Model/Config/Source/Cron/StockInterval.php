<?php
declare(strict_types=1);

namespace Ecommerce66\AiCore\Model\Config\Source\Cron;

use Magento\Framework\Data\OptionSourceInterface;

class StockInterval implements OptionSourceInterface
{
    /** 2/3/4/6 hours; values = CRON expr */
    public function toOptionArray(): array
    {
        return [
            ['value' => '0 */2 * * *', 'label' => __('Every 2 hours')],
            ['value' => '0 */3 * * *', 'label' => __('Every 3 hours')],
            ['value' => '0 */4 * * *', 'label' => __('Every 4 hours')],
            ['value' => '0 */6 * * *', 'label' => __('Every 6 hours')],
        ];
    }
}
