<?php

namespace Swissup\SeoCanonical\Model\ParentFinder;

class GroupedProduct extends \Magento\Catalog\Model\ResourceModel\Product\Link
    implements \Swissup\SeoCanonical\Api\ParentFinderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getParentIds($childId)
    {
        return $this->getParentIdsByChild(
            $childId,
            \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED
        );
    }
}
