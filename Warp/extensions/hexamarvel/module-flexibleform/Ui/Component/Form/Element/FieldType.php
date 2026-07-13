<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Ui\Component\Form\Element;

class FieldType implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            ['label' => 'Text', 'value' => 'text'],
            ['label' => 'Email', 'value' => 'email'],
            ['label' => 'Number', 'value' => 'number'],
            ['label' => 'Textarea', 'value' => 'textarea'],
            ['label' => 'Select', 'value' => 'select'],
            ['label' => 'Multiselect', 'value' => 'multiselect'],
            ['label' => 'Checkbox', 'value' => 'checkbox'],
            ['label' => 'Radio', 'value' => 'radio'],
            ['label' => 'Date', 'value' => 'date'],
            ['label' => 'Time', 'value' => 'time'],
            ['label' => 'Date And Time', 'value' => 'datetime'],
            ['label' => 'File', 'value' => 'file'],
            ['label' => 'Image', 'value' => 'image'],
            ['label' => 'Video', 'value' => 'video'],
            ['label' => 'Image, Video & Files', 'value' => 'all_files'],
            ['label' => 'Terms and Condition', 'value' => 'terms'],
            ['label' => 'Hidden', 'value' => 'hidden'],
            ['label' => 'Country', 'value' => 'country'],
            ['label' => 'State', 'value' => 'state'],
        ];
    }
}
