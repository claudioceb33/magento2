<?php

namespace Swissup\RichSnippets\Model\Product\StructuredData\Offer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Output as AttributeOutput;
use Magento\Store\Model\StoreManagerInterface;
use Swissup\RichSnippets\Model\DataSnippetInterface;
use Swissup\RichSnippets\Model\Product\Config;
use Swissup\RichSnippets\Model\Product\StructuredData\AbstractData;
use Swissup\RichSnippets\Model\Config\Source\MerchantReturnCategory;

class MerchantReturnPolicy extends AbstractData implements DataSnippetInterface
{
    private Config $config;
    private ProductInterface $product;
    private StoreManagerInterface $storeManager;

    public function __construct(
        Config $config,
        ProductInterface $product,
        StoreManagerInterface $storeManager,
        AttributeOutput $attributeOutput
    ) {
        $this->config = $config;
        $this->product = $product;
        $this->storeManager = $storeManager;
        parent::__construct($attributeOutput);
    }

    public function get(array $dataMap = [])
    {
        $storeId = $this->product->getStoreId();
        if (!$returnCategory = $this->config->getMerchantReturnCategory($storeId)) {
            return [];
        }

        $canUseReturnDays = $this->canUseReturnDays($returnCategory);
        $canUseReturnMethod = $this->canUseReturnMethod($returnCategory);
        $returnMethod = $this->config->getMerchantReturnMethod($storeId);
        $isFreeReturn = $this->config->isMerchantReturnNoFee($storeId);
        $policy = array_merge(
            [
                '@type' => 'MerchantReturnPolicy',
                'applicableCountry' => $this->config->getMerchantReturnCountry($storeId),
                'returnPolicyCategory' => "https://schema.org/MerchantReturn{$returnCategory}",
                'merchantReturnDays' => $canUseReturnDays ?
                    $this->config->getMerchantReturnDays($storeId) :
                    0,
                'returnMethod' => $canUseReturnMethod ?
                    "https://schema.org/Return{$returnMethod}" :
                    '',
                'returnFees' => $canUseReturnMethod && $isFreeReturn ?
                    'https://schema.org/FreeReturn' :
                    '',
                'returnShippingFeesAmount' => $canUseReturnMethod && !$isFreeReturn ?
                    $this->getShippingFees($storeId) :
                    ''
            ],
            $this->buildAttributeBasedData($dataMap, $this->product)
        );

        return $policy;
    }

    static public function canUseReturnMethod($returnCategory): bool
    {
        return in_array($returnCategory, [
            MerchantReturnCategory::FINIT_WINDOW,
            MerchantReturnCategory::UNLIM_WINDOW
        ]);
    }

    static function canUseReturnDays($returnCategory): bool
    {
        return $returnCategory === MerchantReturnCategory::FINIT_WINDOW;
    }

    private function getShippingFees($storeId): array
    {
        $store = $this->storeManager->getStore($storeId);
        $currencyCode = $store->getCurrentCurrency()->getCode();
        $fees = $this->config->getMerchantReturnShippingFees($storeId);
        $fees = explode('-', $fees);
        $fees = array_map('trim', $fees);

        if (count($fees) > 1) {
            return [
                '@type' => 'MonetaryAmount',
                'currency' => $currencyCode,
                'minValue' => reset($fees),
                'maxValue' => end($fees),
                'value' => end($fees)
            ];
        }

        return [
            '@type' => 'MonetaryAmount',
            'currency' => $currencyCode,
            'value' => reset($fees)
        ];
    }
}
