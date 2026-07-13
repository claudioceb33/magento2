<?php

namespace Swissup\SeoCanonical\Model\ParentFinder;

use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Swissup\SeoCanonical\Api\ParentFinderInterface;

class ConfigurableProduct extends Configurable implements ParentFinderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getParentIds($childId)
    {
        return $this->getParentIdsByChild($childId);
    }
}
