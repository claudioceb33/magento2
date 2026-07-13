<?php /**
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

use Magento\Framework\Option\ArrayInterface;

class Css implements ArrayInterface
{
    /**
     * @return array|array[]
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'boostrap', 'label' => __('Bootstrap')],
            ['value' => 'foundation', 'label' => __('Foundation')],
        ];
    }
}
