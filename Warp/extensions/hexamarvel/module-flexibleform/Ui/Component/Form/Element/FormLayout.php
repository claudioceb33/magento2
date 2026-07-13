<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Ui\Component\Form\Element;

class FormLayout implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            ['label' => '1 Column', 'value' => '1column'],
            ['label' => '2 Column Left', 'value' => '2column-left'],
            ['label' => '2 Column Right', 'value' => '2column-right']
        ];
    }
}
