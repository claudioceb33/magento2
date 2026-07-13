<?php

namespace Ecommerce66\OTPLogin\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Ecommerce66\OTPLogin\Helper\Data;

class OTPData implements ArgumentInterface
{
    /**
     * @var Data
     */
    protected $data;

    /**
     * @param Data $data
     */
    public function __construct(
        Data $data
    ) {
        $this->data = $data;
    }

    /**
     * @return bool
     */
    public function isEnable() : bool{
        return $this->data->isActive();
    }

    /**
     * @return bool
     */
    public function isDisabled() : bool{
        return !$this->data->isActive();
    }

}
