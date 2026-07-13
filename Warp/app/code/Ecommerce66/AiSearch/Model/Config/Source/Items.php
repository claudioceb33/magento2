<?php
declare(strict_types=1);

namespace Ecommerce66\AiSearch\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Items implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        $opts = [];
        for ($i = 1; $i <= 10; $i++) {
            $opts[] = ['value' => (string)$i, 'label' => (string)$i];
        }
        return $opts;
    }
}
