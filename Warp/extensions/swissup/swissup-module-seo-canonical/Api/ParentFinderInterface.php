<?php

namespace Swissup\SeoCanonical\Api;

interface ParentFinderInterface
{
    /**
     * Retrieve array of parent product ids by selection product id(s)
     *
     * @param int|array $childId
     * @return array
     */
    public function getParentIds($childId);
}
