<?php

namespace Swissup\RichSnippets\Model\Product\StructuredData\Offer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Config\CacheInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Store\Model\StoreManagerInterface;
use Swissup\RichSnippets\Model\DataSnippetInterface;
use Swissup\RichSnippets\Model\Product\Config;

class ShippingDetails implements DataSnippetInterface
{
    private CacheInterface $cache;
    private Config $config;
    private ProductInterface $product;
    private QuoteFactory $quoteFactory;
    private TotalsCollector $totalsCollector;
    private StoreManagerInterface $storeManager;

    private string $currencyCode;
    private array $countryConfig;
    private Quote $quote;

    public function __construct(
        CacheInterface $cache,
        Config $config,
        ProductInterface $product,
        QuoteFactory $quoteFactory,
        TotalsCollector $totalsCollector,
        StoreManagerInterface $storeManager
    ) {
        $this->cache = $cache;
        $this->config = $config;
        $this->product = $product;
        $this->quoteFactory = $quoteFactory;
        $this->totalsCollector = $totalsCollector;
        $this->storeManager = $storeManager;
    }

    public function get()
    {
        if (!$this->product->isSalable()) {
            // Product is not salable.
            // Unable to calculate shipping rates for such product.
            return [];
        }

        $storeId = $this->product->getStoreId();
        $shippingDetailsConfig = $this->config->getShippingDetailsCountryConfig($storeId);

        $cacheIdentifier = $this->getCacheIdentifier();
        $json = $this->cache->load($cacheIdentifier);
        if ($json === false) {
            $json = json_encode($this->buildShippingDetails());
            $this->cache->save($json, $cacheIdentifier);
        }

        return json_decode($json, true);
    }

    private function getCacheIdentifier(): string
    {
        $storeId = $this->product->getStoreId();

        return 'swissupRichSnippetsShipping_' .
            sha1(json_encode([
                $this->product->getId(),
                $storeId,
                $this->config->getShippingDetailsCountryConfig($storeId)
            ]));
    }

    private function buildShippingDetails(): array
    {
        $storeId = $this->product->getStoreId();
        $shippingDetailsConfig = $this->config->getShippingDetailsCountryConfig($storeId);
        $countries = array_keys($shippingDetailsConfig);
        $shippingDetails = array_reduce($countries, function ($details, $country) {
            $rates = $this->getShippingRates($country);

            return array_merge(
                $details,
                array_map([$this, 'prepareDetails'], $rates)
            );
        }, []);

        return $shippingDetails;
    }

    private function prepareDetails(Rate $rate): array
    {
        $storeId = $this->product->getStoreId();
        $address = $rate->getAddress();
        $country = $address->getCountryId();
        $method = $this->getShippingMethodCode($rate);
        $details = [
            '@type' => 'OfferShippingDetails',
            'shippingRate' => [
                '@type' => 'MonetaryAmount',
                'name' => $rate->getMethodTitle(),
                'value' => $rate->getPrice(),
                'currency' => $this->getCurrencyCode()
            ],
            'shippingDestination' => [
                '@type' => 'DefinedRegion',
                'addressCountry' => $country
            ]
        ];

        $config = $this->config->getShippingDetailsCountryConfig($storeId);
        $config = $config[$country][$method] ?? [];
        if (!$config) {
            return $details;
        }

        $details['deliveryTime'] = [
            '@type' => 'ShippingDeliveryTime'
        ];
        $deliveryTime = &$details['deliveryTime'];
        foreach (['handling', 'transit'] as $key) {
            if (!empty($config[$key])) {
                $days = array_map('trim', explode('-', $config[$key]));
                $deliveryTime["{$key}Time"] = [
                    '@type' => 'QuantitativeValue',
                    'minValue' => min($days),
                    'maxValue' => max($days),
                    'unitCode' => 'DAY'
                ];
            }
        }

        return $details;
    }

    private function getCurrencyCode(): string
    {
        if (!isset($this->currencyCode)) {
            $store = $this->storeManager->getStore($this->product->getStoreId());
            $currency = $store->getCurrentCurrency();
            $this->currencyCode = $currency->getCode();
        }

        return $this->currencyCode;
    }

    private function getShippingRates($countryId): array
    {
        // Inspired by code in
        // module-quote/Model/ShippingMethodManagement::getShippingMethods
        $quote = $this->getQuote();
        $address = $quote->getShippingAddress()
            ->setCountryId($countryId)
            ->setCollectShippingRates(true);

        $this->totalsCollector->collectAddressTotals($quote, $address);
        $shippingRates = $address->getGroupedAllShippingRates();
        $rates = [];
        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $rates[] = $rate;
            }
        }

        return $rates;
    }

    private function getQuote()
    {
        if (!isset($this->quote)) {
            $this->quote = $this->quoteFactory
                ->create()
                ->setStoreId($this->product->getStoreId())
                ->setCustomerIsGuest(1);
            $this->quote->addProduct(
                $this->product,
                null,
                \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_LITE
            );
        }

        return $this->quote;
    }

    private function getShippingMethodCode(Rate $rate): string
    {
        $tableRateLikeCarrier = [
            'tablerate', // Default Magento 2 Table Rate delivary method
            'matrixrate' // WebShopApps – MatrixRate
        ];
        $carrier = $rate->getCarrier();
        if (in_array($carrier, $tableRateLikeCarrier)) {
            $method = (string)$rate->getMethod();
            $parts = explode('_', $method);
            if (count($parts) > 1) {
                array_pop($parts);
                $method = implode('_', $parts);
            }

            return $carrier . '_' . $method;
        }

        return $rate->getCode();
    }
}
