<?php

namespace Swissup\RichSnippets\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Output as AttributeOutput;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Swissup\RichSnippets\Model\Config\Backend\StructuredData as ConfigValueProcessor;
use Swissup\RichSnippets\Model\DataSnippetFactory;

class StructuredData extends StructuredData\AbstractData
{
    private ConfigValueProcessor $configValueProcessor;
    private DataSnippetFactory $dataSnippetFactory;
    private ScopeConfigInterface $scopeConfig;
    private StoreManagerInterface $storeManager;
    private array $dataSnippet;

    public function __construct(
        AttributeOutput $attributeOutput,
        ConfigValueProcessor $configValueProcessor,
        DataSnippetFactory $dataSnippetFactory,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        array $dataSnippet = []
    ) {
        $this->configValueProcessor = $configValueProcessor;
        $this->dataSnippetFactory = $dataSnippetFactory;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->dataSnippet = $dataSnippet;
        parent::__construct($attributeOutput);
    }

    /**
     * Get structured data for product
     *
     * @param  ProductInterface $product
     * @return array
     */
    public function get(ProductInterface $product)
    {
        $data = [
            '@context'    => 'http://schema.org',
            '@type'       => 'Product',
            '@id'         => $product->getProductUrl()
        ];

        $dataMap = $this->getDataMap($product->getStoreId());
        // data from config map
        $data = array_merge($data, $this->buildAttributeBasedData($dataMap, $product));

        // predefined data snippets
        $dataSnippets = array_diff_key($this->dataSnippet, $data);
        foreach ($dataSnippets as $snippetName => $snipetClass) {
            $additionalMap = is_array($dataMap[$snippetName] ?? null)
                ? $dataMap[$snippetName]
                : [];
            $data[$snippetName] = $this->dataSnippetFactory
                ->create($snipetClass, $product)
                ->get($additionalMap);
        }

        return $data;
    }

    /**
     * Get snippet data map
     *
     * @param  int $storeId
     * @return array
     */
    public function getDataMap($storeId)
    {
        $store = $this->storeManager->getStore($storeId);
        $configValue = $this->scopeConfig->getValue(
            'richsnippets/product/structured_data',
            ScopeInterface::SCOPE_STORE,
            $store
        );

        $arrayFieldValue = $this->configValueProcessor->makeArrayFieldValue($configValue);
        $dataMap = [];
        foreach ($arrayFieldValue as $item) {
            $keys = explode('/', $item['property']);
            $key = array_shift($keys);
            $value = &$dataMap[$key];
            foreach ($keys as $key) {
                if (!is_array($value)) {
                    $value = [];
                }

                $value = &$value[$key];
            }

            $value = $item['product_attribute'];
        }

        return $dataMap;
    }
}
