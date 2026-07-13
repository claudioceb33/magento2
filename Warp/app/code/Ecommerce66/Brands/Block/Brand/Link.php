<?php
declare(strict_types=1);

namespace Ecommerce66\Brands\Block\Brand;

use Ecommerce66\Brands\Block\Base;
use Ecommerce66\Brands\Model\BrandDetails;

class Link extends Base
{
    /**
     * @return string
     */
    public function getBrandList()
    {
        return $this->helperData->getBrandList();
    }

    /**
     * @param int $brandOptionId
     */
    public function getBrandUrlDetails($brandOptionId)
    {
        return $this->helperData->getBrandUrlDetails($brandOptionId);
    }

    /**
     * @return string
     */
    public function getBrandAttributeCode()
    {
        return $this->helperData->getBrandAttributeCode();
    }
}
