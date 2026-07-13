<?php
declare(strict_types=1);

namespace Ecommerce66\Brands\Model\ResourceModel\BrandDetails;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'brand_details_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Ecommerce66\Brands\Model\BrandDetails::class,
            \Ecommerce66\Brands\Model\ResourceModel\BrandDetails::class
        );
    }
}
