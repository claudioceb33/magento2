<?php

namespace Swissup\RichSnippets\Model\Product;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Swissup\RichSnippets\Model\Config\Source\PriceValidUntil;
use Swissup\RichSnippets\Model\Config\Backend\ShippingDetails as ShippingDetailsCountryConfigProcessor;

class Config
{
    protected ScopeConfigInterface $scopeConfig;
    protected ShippingDetailsCountryConfigProcessor $shippingDetailsCountryConfigProcessor;
    protected StoreManagerInterface $storeManager;

    private array $shippingDetailsCountryConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ShippingDetailsCountryConfigProcessor $shippingDetailsCountryConfigProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->shippingDetailsCountryConfigProcessor = $shippingDetailsCountryConfigProcessor;
        $this->storeManager = $storeManager;
    }

    private function getValue(string $path, $storeId): string
    {
        $store = $this->storeManager->getStore($storeId);

        return (string)$this->scopeConfig->getValue(
            "richsnippets/product/{$path}",
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    private function isSetFlag(string $path, $storeId): bool
    {
        $store = $this->storeManager->getStore($storeId);

        return $this->scopeConfig->isSetFlag(
            "richsnippets/product/{$path}",
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getValidUntil($storeId): string
    {
        $type = (int)$this->getValue('price_valid/type', $storeId);
        if ($type === PriceValidUntil::DYNAMIC_DATE) {
            $offset = (int)$this->getValue('price_valid/offset', $storeId);
            $date = new \DateTime();
            $date->modify("+{$offset} days");
            $validUntil = $date->format('Y-m-d');
        } else {
            $validUntil = $this->getValue('price_valid/until', $storeId);
        }

        return $validUntil;
    }

    public function getConditionAttributeCode($storeId): string
    {
        return $this->getValue('condition/attribute', $storeId);
    }

    public function getConditionOptions($storeId): array
    {
        $options = array_flip(['new', 'used', 'damaged', 'refurbished']);
        array_walk($options, function (&$value, $key) use ($storeId) {
            $value = $this->getValue("condition/{$key}_option", $storeId);
        });

        return array_filter($options);
    }

    public function isAvailabilityEnabled($storeId): bool
    {
        return $this->isSetFlag('add_availability_data', $storeId);
    }

    public function getMerchantReturnCategory($storeId): string
    {
        return $this->getValue('return_policy/category', $storeId);
    }

    public function getMerchantReturnCountry($storeId): array
    {
        $country = $this->getValue('return_policy/country', $storeId);
        $country = explode(',', $country);

        return array_map('trim', $country);
    }

    public function getMerchantReturnDays($storeId): int
    {
        return (int)$this->getValue('return_policy/return_days', $storeId);
    }

    public function getMerchantReturnMethod($storeId): string
    {
        return $this->getValue('return_policy/method', $storeId);
    }

    public function isMerchantReturnNoFee($storeId): bool
    {
        return $this->isSetFlag('return_policy/no_fee', $storeId);
    }

    public function getMerchantReturnShippingFees($storeId): string
    {
        return $this->getValue('return_policy/shipping_fees', $storeId);
    }

    public function getShippingDetailsCountryConfig($storeId): array
    {
        if (!isset($this->shippingDetailsCountryConfig)) {
            $value = $this->getValue('shipping_details/country_config', $storeId);
            $arrayFieldValue = $this->shippingDetailsCountryConfigProcessor
                ->makeArrayFieldValue($value);

            $this->shippingDetailsCountryConfig = [];
            foreach ($arrayFieldValue as $data) {
                $country = $data['country'];
                $method = $data['method'];
                unset($data['country']);
                unset($data['method']);
                $this->shippingDetailsCountryConfig[$country][$method] = $data;
            }
        }

        return $this->shippingDetailsCountryConfig;
    }

    public function getImageId($storeId): string
    {
        return $this->getValue('image/id', $storeId);
    }
}
