<?php

namespace Swissup\SeoUrls\Plugin\Swatches\Block\Product\Renderer\Listing;

use Magento\Catalog\Model\ResourceModel\Product as ResourceProduct;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Json\DecoderInterface;
use Magento\Swatches\Block\Product\Renderer\Listing\Configurable as Subject;
use Swissup\SeoUrls\Model\Attribute as SeoAttribute;
use Swissup\SeoUrls\Helper\Data as Helper;

class Configurable
{
    private Helper $helper;
    private ResourceProduct $resourceProduct;
    private SeoAttribute $seoAttribute;
    private EncoderInterface $encoder;
    private DecoderInterface $decoder;

    public function __construct(
        Helper $helper,
        ResourceProduct $resourceProduct,
        SeoAttribute $seoAttribute,
        EncoderInterface $encoder,
        DecoderInterface $decoder
    ) {
        $this->helper = $helper;
        $this->resourceProduct = $resourceProduct;
        $this->seoAttribute = $seoAttribute;
        $this->encoder = $encoder;
        $this->decoder = $decoder;
    }

    public function afterGetJsonSwatchConfig(
        Subject $subject,
        string $result
    ): string {
        if ($this->helper->isSeoUrlsEnabled()
            && $config = $this->decoder->decode($result)
        ) {
            foreach ($config as $swatchId => &$swatch) {
                $attribute = $this->resourceProduct->getAttribute($swatchId);
                // set in-URL label for attribute
                $swatch['inUrlLabel'] = $this->seoAttribute->getStoreLabel($attribute);
                foreach ($swatch as $itemId => &$item) {
                    if (!is_array($item)) {
                        continue;
                    }

                    $option = new \Magento\Framework\DataObject([
                        'value' => $itemId,
                        'label' => isset($item['label']) ? $item['label'] : ''
                    ]);
                    // set in-URL value for attribute
                    $item['inUrlValue'] = $this->seoAttribute->getStoreValue(
                        $attribute,
                        $option
                    );
                }

            }

            $result = $this->encoder->encode($config);
        }

        return $result;
    }
}
