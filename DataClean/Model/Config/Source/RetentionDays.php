<?php
namespace Ceb\DataClean\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class RetentionDays implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '30', 'label' => '30 Days'],
            ['value' => '60', 'label' => '60 Days'],
            ['value' => '90', 'label' => '90 Days'],
        ];
    }
}
