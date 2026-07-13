<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_Formbuilder
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\Formbuilder\Model\Config\Source;

class DateFormat
{
    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => 'd/m/Y',
                'label' => __('dd/mm/yyyy'),
            ],
            [
                'value' => 'm/d/Y',
                'label' => __('mm/dd/yyyy'),
            ],
            [
                'value' => 'm-d-y',
                'label' => __('mm-dd-yy'),
            ]
        ];
    }
}
