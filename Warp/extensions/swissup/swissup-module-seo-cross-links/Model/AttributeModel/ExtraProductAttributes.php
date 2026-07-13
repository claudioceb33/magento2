<?php

namespace Swissup\SeoCrossLinks\Model\AttributeModel;

use Swissup\SeoCrossLinks\Model\ResourceModel\Link\CollectionFactory as LinksCollection;

class ExtraProductAttributes extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var LinksCollection
     */
    protected $linksCollection;

    /**
     * @var array
     */
    private $extraProductAttributes;

    /**
     * @param LinksCollection
     */
    public function __construct(LinksCollection $linksCollection)
    {
        $this->linksCollection = $linksCollection;
    }

    /**
     * Get links option 'extra_attributes'
     * @return array
     */
    public function getExtraProductAttributes()
    {
        if (!isset($this->extraProductAttributes)) {
            $collection = $this->linksCollection->create()
                ->addFieldToFilter('is_active', 1);

            $this->extraProductAttributes = $collection->getColumnValues('extra_attributes');
            $this->extraProductAttributes = array_filter($this->extraProductAttributes);
            $this->extraProductAttributes = array_unique($this->extraProductAttributes);
        }

        return $this->extraProductAttributes;
    }
}
