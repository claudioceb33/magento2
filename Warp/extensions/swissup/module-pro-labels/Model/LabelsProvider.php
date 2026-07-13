<?php

namespace Swissup\ProLabels\Model;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;
use Swissup\ProLabels\Helper\Data as Helper;
use Swissup\ProLabels\Model\Config\Source\OutputStrategy;
use Swissup\ProLabels\Model\LabelsProvider\Variables;

class LabelsProvider
{
    protected Helper $helper;
    protected Label $labelModel;
    protected StoreManagerInterface $storeManager;
    protected CustomerSession $customerSession;
    protected DataObject $collection;

    private Variables $variables;
    private array $cachedManual = [
        'category' => [],
        'product' => []
    ];

    public function __construct(
        Helper $helper,
        Label $labelModel,
        StoreManagerInterface $storeManager,
        CustomerSession $customerSession,
        Variables $variables
    ) {
        $this->helper = $helper;
        $this->labelModel = $labelModel;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->variables = $variables;

        $this->collection = new DataObject();
    }

    /**
     * Get initilized labels for product and mode
     *
     * @param  string $memoizationKey
     * @param  string $mode
     * @return \Magento\Framework\DataObject
     */
    public function getLabels($memoizationKey, $mode)
    {
        return $this->collection->getData("{$memoizationKey}::{$mode}");
    }

    /**
     * Initilize labels for product in $mode
     * @param  Product                        $product
     * @param  string                         $mode
     * @return \Magento\Framework\DataObject
     */
    public function initialize(Product $product, $mode)
    {
        $memoizationKey = $this->getMemoizationKey($product);
        $labels = $this->getLabels($memoizationKey, $mode);
        if (!$labels) {
            $labels = [];
            $this->initSystemLabels($labels, $product, $mode);
            $this->initManualLabels($labels, $product, $mode);

            $store = $this->storeManager->getStore();
            $outputStrategy = $store->getConfig('prolabels/output/strategy');
            foreach ($labels as &$positionedLabels) {
                // order labels
                usort($positionedLabels, function ($labelA, $labelB) {
                    return (float)$labelA['sort_order'] <=> (float)$labelB['sort_order'];
                });

                if ($outputStrategy === OutputStrategy::SINGLE) {
                    // if labels output startegy is 'single'
                    // then output label with highest sort_order
                    $positionedLabels = [array_pop($positionedLabels)];
                }
            }

            $labels = new DataObject(
                [
                    'labels_data' => $this->prepareLabelsData($labels, $mode),
                    'predefined_variables' => $this->variables->collect($labels, $product)
                ]
            );
            $this->collection->setData("{$memoizationKey}::{$mode}", $labels);
        }

        return $labels;
    }

    /**
     * Prepare memoization key for prolabels data.
     *
     * (Used in soldtogether module)
     *
     * @param  Product $product
     * @return string
     */
    private function getMemoizationKey(Product $product)
    {
        return $product->getData('prolabels_data/memoization_key') ?:
            $product->getId();
    }

    /**
     * @param  array  $labels
     * @param  string $mode
     * @return array
     */
    protected function prepareLabelsData($labels, $mode)
    {
        $labelsData = [];

        foreach ($labels as $position => $labels) {
            $items = [];
            list($position, $targetElement, $insertMethod) = explode('|', $position) + ['', '', ''];
            foreach ($labels as $label) {
                $data = $label->getData();
                unset($data['position']); // no need in this value
                // get label image URL
                $data['image'] = $label->getImage()
                    ? $this->getLabelImage($label->getImage(), $mode)
                    : null;
                $data['custom'] = $label->getCustom()
                    ? preg_replace("/\s+/", " ", $label->getCustom())
                    : null;

                $items[] = array_filter($data); // remove empty values
            }

            if (!empty($items)) {
                $labelsData[] = array_filter([
                    'position' => $position,
                    'target' => $targetElement ? [
                        'element' => $targetElement,
                        'method' => $insertMethod,
                    ] : [],
                    'items' => $items
                ]);
            }
        }

        return $labelsData;
    }

    /**
     * @param  array   &$labels
     * @param  Product $product
     * @param  string  $mode
     * @return $this
     */
    protected function initSystemLabels(&$labels, Product $product, $mode)
    {
        $systemLabels = $this->helper->getSystemLabelsInstance();
        if ($this->isSystemLabelAllowed('on_sale')
            && $onSale = $systemLabels->getOnSaleLabel($product, $mode)
        ) {
            $labels[$onSale->getPosition()][] = $onSale;
        }

        if ($this->isSystemLabelAllowed('is_new')
            && $isNew = $systemLabels->getIsNewLabel($product, $mode)
        ) {
            $labels[$isNew->getPosition()][] = $isNew;
        }

        if ($this->isSystemLabelAllowed('in_stock')
            && $inStock = $systemLabels->getStockLabel($product, $mode)
        ) {
            $labels[$inStock->getPosition()][] = $inStock;
        }

        if ($this->isSystemLabelAllowed('out_stock')
            && $outOfStock = $systemLabels->getOutOfStockLabel($product, $mode)
        ) {
            $labels[$outOfStock->getPosition()][] = $outOfStock;
        }

        return $this;
    }

    /**
     * @param  array   &$labels
     * @param  Product $product
     * @param  string  $mode
     * @return $this
     */
    protected function initManualLabels(&$labels, Product $product, $mode)
    {
        $collectedLabels = $this->_getManualLabels([$product->getId()], $mode);
        $manualLabels = $collectedLabels[$product->getId()] ?? [];
        $systemLabels = $this->helper->getSystemLabelsInstance();
        foreach ($manualLabels as $label) {
            $labelData = $systemLabels->getLabelOutputObject(
                $label->getData()
            );
            $key = $labelData->getPosition() == 'content' ?
                rtrim(implode('|', [
                    $labelData->getPosition(),
                    $labelData->getTargetElement(),
                    $labelData->getInsertMethod()
                ]), '|') :
                $labelData->getPosition();
            $labels[$key][] = $labelData;
        }

        return $this;
    }

    /**
     * Get prolabels image URL
     *
     * @param  string $image
     * @param  string $mode
     * @return string
     */
    public function getLabelImage($image, $mode = 'product')
    {
        $systemLabels = $this->helper->getSystemLabelsInstance();

        return $systemLabels->getUploadedLabelImage($image, $mode);
    }

    /**
     * Is system label $key visible
     *
     * @param  string  $key
     * @return boolean
     */
    private function isSystemLabelAllowed($key)
    {
        $customerGroupId = (string)$this->customerSession->getCustomerGroupId();
        $store = $this->storeManager->getStore();
        $excludeGroups = explode(
            ',',
            (string) $store->getConfig("prolabels/{$key}/exclude_customer_group")
        );

        return !in_array($customerGroupId, $excludeGroups);
    }

    /**
     * Preload (warm) manual labels data for collection (array) of products
     * @param  Product[]  $products
     * @param  string     $mode
     * @return $this
     */
    public function preloadManualForProducts(array $products, $mode)
    {
        $ids = array_map(function ($item) {
            return $item->getId();
        }, $products);

        $this->_getManualLabels($ids, $mode);

        return $this;
    }

    /**
     * Get manual labels data for product ids
     *
     * @param  array  $productIds
     * @param  string $mode
     * @return array
     */
    private function _getManualLabels(array $productIds, $mode)
    {
        // Make sure productIds array has only INTEGER or STRING values
        $productIds = array_filter($productIds, 'is_scalar');
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
}
