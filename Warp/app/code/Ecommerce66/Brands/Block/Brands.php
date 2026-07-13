<?php
declare(strict_types=1);

namespace Ecommerce66\Brands\Block;

use Ecommerce66\Brands\Model\BrandDetails;

class Brands extends Base
{
    /**
     * @return string
     */
    public function getBrandList()
    {
        return $this->helperData->getBrandList();
    }

    /**
     * @param BrandDetails $brand
     */
    public function getBrandUrl(BrandDetails $brand)
    {
        return $this->helperData->getBrandUrl($brand);
    }
}
