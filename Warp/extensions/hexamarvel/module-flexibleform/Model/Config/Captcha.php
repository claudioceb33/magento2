<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */
namespace Hexamarvel\FlexibleForm\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;

class Captcha implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $options = [
            ['label' => __('--Select Captcha Type--'), 'value' => ''],
            ['label' => __('Magento Captcha'), 'value' => 'magento'],
            ['label' => __('Google Captcha'), 'value' => 'google']
        ];
        return $options;
    }
}
