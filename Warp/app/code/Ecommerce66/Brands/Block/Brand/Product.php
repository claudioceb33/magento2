<?php
declare(strict_types=1);

namespace Ecommerce66\Brands\Block\Brand;

use Ecommerce66\Brands\Block\Base;

class Product extends Base
{
    /**
     * @param $product
     *
     * @return \Magento\Framework\DataObject|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBrandByProduct($product)
    {
        $brand = null;
        if (is_object($product)) {
            $brandAttributeCode = $this->helperData->getBrandAttributeCode();
            $brandOptionId = $product->getData($brandAttributeCode);
            $brand = $this->helperData
                ->getBrandList(['option_id' => (int)$brandOptionId])
                ->getFirstItem();
        }

        return $brand;
    }
}
