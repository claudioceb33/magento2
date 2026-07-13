<?php
declare(strict_types=1);

namespace Ecommerce66\Brands\Block\Brand;

use Ecommerce66\Brands\Block\Base;

class Details extends Base
{
    /**
     * @return \Magento\Catalog\Model\Category
     */
    public function getCurrentCategory()
    {
        return $this->layerResolver->get()->getCurrentCategory();
    }

    /**
     * @param $categoryId
     *
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBrandByCategoryId($categoryId)
    {
        return $this->helperData
            ->getBrandList(['category_id' => (int)$categoryId])
            ->getFirstItem();
    }
}
