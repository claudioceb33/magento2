<?php

namespace Swissup\ProLabelsConfigurableProduct\Plugin\ResourceModel;

use Swissup\ProLabels\Model\ResourceModel\Label as Subject;
use Swissup\ProLabelsConfigurableProduct\Model\ResourceModel\Label\Configurable as ResourceConfigurableLabels;

class Label
{
    private ResourceConfigurableLabels $resourceConfigurableLabels;

    public function __construct(
        ResourceConfigurableLabels $resourceConfigurableLabels
    ) {
        $this->resourceConfigurableLabels = $resourceConfigurableLabels;
    }

    public function afterGetProductLabels(
        Subject $subject,
        array $result,
        $productId,
        $storeId = 0,
        $customerGroupId = 0,
        $mode = 'product'
    ) {
        $childLabels = $this->resourceConfigurableLabels
            ->getChildLabels($productId, $storeId, $customerGroupId, $mode);
        // append super product with labels from child products
        // if such option for label is enabled
        foreach ($childLabels as $parentId => $labels) {
            if (!isset($result[$parentId])) {
                $result[$parentId] = [];
            }

            $parentLabels = &$result[$parentId];
            foreach ($labels as $label) {
                $sameLabels = array_filter($parentLabels, function ($parentLabel) use ($label) {
                    return $parentLabel->toArray() === $label->toArray();
                });

                if (empty($sameLabels)) {
                    $parentLabels[] = $label;
                }
            }
        }

        return $result;
    }
}
