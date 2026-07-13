<?php

namespace Swissup\SeoCrossLinks\Model\AttributeModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as ProductAttributeCollection;

class ProductAttributes extends AbstractCollection
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute
     */
    protected $productAttributes;

    /**
     * @var array
     */
    private $attributeData;

    /**
     * @param ProductAttributeCollection
     */
    public function __construct(ProductAttributeCollection $productAttributes)
    {
        $this->productAttributes = $productAttributes;
    }

    /**
     * @return array product attributes
     */
    public function afterGetProductAttributes()
    {
        if (!isset($this->attributeData)) {
            $collection = $this->productAttributes->create()
                ->addFieldToFilter('is_html_allowed_on_front', 1);

            $this->attributeData = $collection->getColumnValues('attribute_code');
        }

        return $this->attributeData;
    }
}
