<?php

namespace Demo66\Swatches\Plugin\Model;

use Magento\Framework\Api\SimpleDataObjectConverter as Converter;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;

class LabelsProvider extends \Swissup\ProLabels\Model\LabelsProvider
{
    private $cachedManual = [
        'category' => [],
        'product' => []
    ];

    /**
     * Get initilized labels for product and mode
     *
     * @param  int    $productId
     * @param  string $mode
     * @return \Magento\Framework\DataObject
     */
    public function getLabels($productId, $mode)
    {
        if ($this->collection === null) {
            $this->collection = new DataObject();
        }

        return $this->collection->getData("{$productId}::{$mode}");
    }

    /**
     * Initilize labels for product in $mode
     * @param  Product                        $product
     * @param  string                         $mode
     * @return \Magento\Framework\DataObject
     */
    public function initialize(Product $product, $mode)
    {
        $labels = $this->getLabels($product->getId(), $mode);
        if (!$labels) {
            $labels = [];
            $this->initSystemLabels($labels, $product, $mode);
            $this->initManualLabels($labels, $product, $mode);

            if ($product->getHideLabel()) {
                foreach ($labels as $key => $labels2) {
                    foreach ($labels2 as $key2 => $label) {
                        if (strstr($label['text'], 'discount')) unset($labels[$key][$key2]);
                    }
                }
            }

            $labels = new DataObject(
                [
                    'labels_data' => $this->prepareLabelsData($labels, $mode),
                    'predefined_variables' => $this->preparePredefinedVariables($labels, $product)
                ]
            );

            if ($this->collection === null) {
                $this->collection = new DataObject();
            }

            $this->collection->setData("{$product->getId()}::{$mode}", $labels);
        }

        return $labels;
    }

    /**
     * Is system label $key visible
     *
     * @param  string  $key
     * @return boolean
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function isSystemLabelAllowed($key)
    {
        $customerGroupId = (string)$this->customerSession->getCustomerGroupId();
        $store = $this->storeManager->getStore();
        $value = $store->getConfig("prolabels/{$key}/exclude_customer_group");
        $excludeGroups = explode(
            ',',
            !empty($value) ? $value : ''
        );

        return !in_array($customerGroupId, $excludeGroups);
    }

    /**
     * Get manual labels data for product ids
     *
     * @param  array  $productIds
     * @param  string $mode
     * @return array
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function _getManualLabels(array $productIds, $mode)
    {
        if (array_diff($productIds, array_keys($this->cachedManual[$mode]))) {
            $manualLabels = $this->labelModel->getProductLabels(
                $productIds,
                $this->storeManager->getStore()->getId(),
                $this->customerSession->getCustomerGroupId(),
                $mode
            );
            foreach ($productIds as $productId) {
                $this->cachedManual[$mode][$productId] = $manualLabels[$productId] ?? [];
            }
        }

        return array_intersect_key($this->cachedManual[$mode], array_flip($productIds));
    }

    /**
     * Prepare predefined variables for labels.
     *
     * @param array $labels
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    protected function preparePredefinedVariables(array $labels, Product $product)
    {
        // Minimal safe implementation: return empty array to avoid fatal when parent does not provide it.
        return [];
    }
}
