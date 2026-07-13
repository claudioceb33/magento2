<?php

namespace Ecommerce66\MultilineDiscount\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Ecommerce66\MultilineDiscount\Helper\Data;

class ModuleEnabled implements ArgumentInterface
{
    public function __construct(
        protected Data $dataHelper
    ) { }

    public function getIsEnabled()
    {
        return $this->dataHelper->getIsActive();
    }
}
