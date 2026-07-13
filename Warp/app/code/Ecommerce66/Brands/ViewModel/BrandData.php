<?php

namespace Ecommerce66\Brands\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Ecommerce66\Brands\Helper\Data;

class BrandData implements ArgumentInterface {

    protected $brandHelper;
    /**
     * @param Data $data
     */
    public function __construct(
         Data $data
    )
    {
        $this->brandHelper = $data;
    }

    /**
     * @param $brandOptionId)
     *
     * @return mixed
     */
    public function getBrandUrlDetailsViewModel($brandOptionId){
        return $this->brandHelper->getBrandUrlDetails($brandOptionId);
    }
}
