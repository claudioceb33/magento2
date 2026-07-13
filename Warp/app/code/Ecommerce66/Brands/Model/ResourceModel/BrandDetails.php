<?php
declare(strict_types=1);

namespace Ecommerce66\Brands\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class BrandDetails extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('ecommerce66_brands_brand_details', 'brand_details_id');
    }
}
