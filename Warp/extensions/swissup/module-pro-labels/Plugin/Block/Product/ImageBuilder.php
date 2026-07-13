<?php

namespace Swissup\ProLabels\Plugin\Block\Product;

class ImageBuilder
{
    protected $productId;

    /**
     * @var \Swissup\ProLabels\Model\LabelsProvider
     */
    private $labelsProvider;

    /**
     * @param \Swissup\ProLabels\Model\LabelsProvider $labelsProvider
     */
    public function __construct(
        \Swissup\ProLabels\Model\LabelsProvider $labelsProvider
    ) {
        $this->labelsProvider = $labelsProvider;
    }

    /**
     * @param  \Magento\Catalog\Block\Product\ImageBuilder $subject
     * @param  \Magento\Catalog\Model\Product              $product
     * @return null
     */
    public function beforeSetProduct(
        \Magento\Catalog\Block\Product\ImageBuilder $subject,
        \Magento\Catalog\Model\Product $product
    ) {
        $this->productId = $product->getId();
        $this->labelsProvider->initialize($product, 'category');
        return null;
    }

    /**
     * @param  \Magento\Catalog\Block\Product\ImageBuilder $subject
     * @param  \Magento\Catalog\Block\Product\Image        $result
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function afterCreate(
        \Magento\Catalog\Block\Product\ImageBuilder $subject,
        \Magento\Catalog\Block\Product\Image $result,
        \Magento\Catalog\Model\Product $product = null
    ) {
        if (!$result->hasProductId()) {
            $result->setProductId($this->productId);
        }

        if ($product) {
            $memoizationKey = $product->getData('prolabels_data/memoization_key');
            if ($memoizationKey) {
                $result->setData('prolabels_memoization_key', $memoizationKey);
            }

            $this->labelsProvider->initialize($product, 'category');
        }

        return $result;
    }
}
