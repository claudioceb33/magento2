<?php
declare(strict_types=1);

namespace Ecommerce66\Brands\Block\Brand;

use Ecommerce66\Brands\Block\Base;

use Ecommerce66\Brands\Model\BrandDetails;

class Slider extends Base
{
    /**
     * @return array
     */
    public function getSliderBrands()
    {
        return $this->helperData->getConfigSliderBrands();
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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBrandIndexUrl()
    {
        return $this->helperData->getBrandIndexLink();
    }
}
