<?php

namespace Swissup\SeoCanonical\Model\ParentFinder;

class BundleProduct extends \Magento\Bundle\Model\ResourceModel\Selection
    implements \Swissup\SeoCanonical\Api\ParentFinderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getParentIds($childId)
    {
        return $this->getParentIdsByChild($childId);
    }
}
