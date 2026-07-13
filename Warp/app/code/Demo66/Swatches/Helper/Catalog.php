<?php

namespace Demo66\Swatches\Helper;

class Catalog extends \Swissup\ProLabels\Helper\Catalog
{
    /**
     * @param $_product \Magento\Catalog\Model\Product
     * @return string
     */
    public function toHtmlConfigurableProductLabels($_product, $mode = 'product')
    {
            $initialConfig = [
                'parent' => $this->getListItemSelector(),
                'imageLabelsTarget' => $this->getImageSelector(),
                'contentLabelsTarget' => $this->getContentSelector(),
                    'contentLabelsInsertion' => 'insertAfter'
            ];
            $childProducts = $_product->getTypeInstance()->getUsedProducts($_product);
            $this->preloadLabels(array_merge([$_product], $childProducts));
            $superlabels = $this->getLabels($_product, 'category');
            $superOptions = [];
            if ($superlabels->getLabelsData()) {
                  $superOptions = [
                      'labelsData' => $superlabels->getLabelsData(),
                      'predefinedVars' => $superlabels->getPredefinedVariables()
                  ] + $initialConfig;
            }

             $labels = [];
            if ($superOptions) {
                 $labels = [$_product->getId() => $superOptions];
            }

            foreach ($childProducts as $product) {
                 $childLabels = $this->getLabels($product, 'category');
                if ($childLabels->getLabelsData()) {
                    $labels[$product->getId()] = [
                        'labelsData' => $childLabels->getLabelsData(),
                        'predefinedVars' => $childLabels->getPredefinedVariables()
                    ] + $initialConfig;
                }
            }

        // Render labels with JS. Init jquery widget.
            $mageInit = [
                'Demo66_Swatches/js/prolabels-configurable' => [
                 'swatchOptions' => '[data-role=swatch-option-'.$_product->getId().'] .swatch-option',
                 'configurableOptions' => '#product_addtocart_form .field.configurable',
                 'superProduct' => $_product->getId(),
                 'labels' => $labels
                ]
            ];

            return "<div data-mage-init='{$this->jsonEncode($mageInit)}'></div>";
    }

    public function preloadLabels(array $products)
    {
        $this->labelsProvider->preloadManualForProducts($products, 'product');
    }
}
