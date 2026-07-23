<?php
/**
 * Ceb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Ceb.com license that is
 * available through the world-wide-web at this URL:
 * https://www.ceb.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ceb
 * @package     Ceb_OrderCancel
 * @copyright   Copyright (c) Ceb (https://www.ceb.com/)
 * @license     https://www.ceb.com/LICENSE.txt
 */

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Ceb_OrderCancel',
    __DIR__
);
