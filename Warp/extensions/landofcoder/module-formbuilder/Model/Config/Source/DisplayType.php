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

use Magento\Framework\Option\ArrayInterface;

class DisplayType implements ArrayInterface
{
    /**
     * @var array
     */
    protected array $options;

    /**
     * @var \Lof\Formbuilder\Model\Form
     */
    protected \Lof\Formbuilder\Model\Form $collectionFactory;

    /**
     * DisplayType constructor.
     * @param \Lof\Formbuilder\Model\Form $collectionFactory
     */
    public function __construct(
        \Lof\Formbuilder\Model\Form $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $data = [];

        $data[] = [
            'value' => 'popup',
            'label' => __('Popup'),
        ];

        $data[] = [
            'value' => 'animation',
            'label' => __('Animation'),
        ];

        $data[] = [
            'value' => 'link',
            'label' => __('Button Link'),
        ];

        $data[] = [
            'value' => 'current_page',
            'label' => __('Show on current page')
        ];

        return $data;
    }
}
