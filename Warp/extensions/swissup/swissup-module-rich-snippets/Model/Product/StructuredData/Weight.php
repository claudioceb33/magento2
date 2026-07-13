<?php

namespace Swissup\RichSnippets\Model\Product\StructuredData;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Swissup\RichSnippets\Model\ChildProductsProvider;
use Swissup\RichSnippets\Model\DataSnippetInterface;

class Weight implements DataSnippetInterface
{
    /**
     * @var ChildProductsProvider
     */
    private $childProductsProvider;

    /**
     * @var DirectoryHelper
     */
    private $helper;

    /**
     * @var ProductInterface
     */
    protected $product;

    /**
     * @param ChildProductsProvider $childProductsProvider
     * @param DirectoryHelper       $helper
     * @param ProductInterface      $product
     */
    public function __construct(
        ChildProductsProvider $childProductsProvider,
        DirectoryHelper $helper,
        ProductInterface $product
    ) {
        $this->childProductsProvider = $childProductsProvider;
        $this->helper = $helper;
        $this->product = $product;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        $weight = $this->product->getWeight();
        $minWeight = null;
        $maxWeight = null;

        $children = [];
        if ('configurable' === $this->product->getTypeId()) {
            $children = $this->childProductsProvider->getChildren($this->product);
            // filter only children with weight
            $children = array_filter($children, function ($child) {
                return !is_null($child->getWeight());
            });

            foreach ($children as $child) {
                if (is_null($minWeight)
                    || $minWeight > (float)$child->getWeight()
                ) {
                    $minWeight = (float)$child->getWeight();
                }

                if (is_null($maxWeight)
                    || $maxWeight < (float)$child->getWeight()
                ) {
                    $maxWeight = (float)$child->getWeight();
                }
            }

            if (!is_null($minWeight) && !is_null($maxWeight)) {
                if ($minWeight == $maxWeight) {
                    $weight = $minWeight;
                    $minWeight = null;
                    $maxWeight = null;
                } else {
                    $weight = null;
                }
            }
        }

        if (!$weight && !$minWeight && !$maxWeight) {
            return [];
        }

        $data = [
            '@type' => 'QuantitativeValue',
            'value' => (float)$weight,
            'minValue' => (float)$minWeight,
            'maxValue' => (float)$maxWeight,
            'unitText' => $this->helper->getWeightUnit()
        ];

        return array_filter($data);
    }
}
