<?php

namespace Swissup\RichSnippets\Model\Product\StructuredData;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NotFoundException;
use Swissup\RichSnippets\Model\ChildProductsProvider;
use Swissup\RichSnippets\Model\DataSnippetInterface;
use Swissup\RichSnippets\Model\DataSnippetFactory;

class Offers extends AbstractData implements DataSnippetInterface
{
    protected ProductInterface $product;
    protected ChildProductsProvider $childProductsProvider;
    private DataSnippetFactory $dataSnippetFactory;
    private array $dataSnippet;

    public function __construct(
        ChildProductsProvider $childProductsProvider,
        DataSnippetFactory $dataSnippetFactory,
        ProductInterface $product,
        \Magento\Catalog\Helper\Output $attributeOutput,
        array $dataSnippet = []
    ) {
        $this->product = $product;
        $this->childProductsProvider = $childProductsProvider;
        $this->dataSnippetFactory = $dataSnippetFactory;
        $this->dataSnippet = $dataSnippet;
        parent::__construct($attributeOutput);
    }

    /**
     * Get offers of product for Google Structured Data
     *
     * @param  array  $dataMap
     * @return array
     */
    public function get(array $dataMap = [])
    {
        if (!$this->product->getId()) {
            throw new NotFoundException(__('Product not found.'));
        }

        switch ($this->product->getTypeId()) {
            case 'configurable':
                $products = $this->childProductsProvider->getChildren($this->product);
                $dataMap += ['name' => 'name', 'sku' => 'sku'];
                break;

            case 'grouped':
                $products = $this->product
                    ->getTypeInstance()
                    ->getAssociatedProducts($this->product);
                $dataMap += ['name' => 'name', 'sku' => 'sku'];
                break;

            default:
                $products = [$this->product];
                break;
        }

        $offers = [];
        foreach ($products as $product) {
            $offer = array_merge(
                [
                    '@type' => 'Offer',
                    'url' => $this->product->getProductUrl()
                ],
                $this->buildAttributeBasedData($dataMap, $product)
            );

            // predefinde snippets for offer
            $dataSnippets = array_diff_key($this->dataSnippet, $offer);
            foreach ($dataSnippets as $snippetName => $snipetClass) {
                $additionalMap = is_array($dataMap[$snippetName] ?? null)
                    ? $dataMap[$snippetName]
                    : [];
                $offer[$snippetName] = $this->dataSnippetFactory
                    ->create($snipetClass, $product)
                    ->get($additionalMap);
            }

            $offers[] = array_filter($offer);
        }

        // if (count($offers) > 1) {
        //     $prices = array_column($offers, 'price');
        //     $offers = [
        //         '@type' => 'AggregateOffer',
        //         'offerCount' => count($offers),
        //         'lowPrice' => min($prices),
        //         'highPrice' => max($prices),
        //         'priceCurrency' => reset($offers)['priceCurrency'],
        //         'offers' => $offers
        //     ];
        // }

        return count($offers) === 1 ?
            reset($offers) :
            $offers;
    }
}
