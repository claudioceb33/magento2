<?php
declare(strict_types=1);

namespace Ecommerce66\Widgets\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }
}
