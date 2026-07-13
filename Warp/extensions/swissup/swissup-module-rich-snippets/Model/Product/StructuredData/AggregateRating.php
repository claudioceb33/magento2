<?php

namespace Swissup\RichSnippets\Model\Product\StructuredData;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Store\Model\StoreManagerInterface;
use Swissup\RichSnippets\Model\DataSnippetInterface;

class AggregateRating extends AbstractData implements DataSnippetInterface
{
    /**
     * @var ProductInterface
     */
    protected $product;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Review\Model\Review\SummaryFactory
     */
    protected $reviewSummaryFactory;

    /**
     * @param ProductInterface                            $product
     * @param StoreManagerInterface                       $storeManager
     * @param \Magento\Review\Model\Review\SummaryFactory $reviewSummaryFactory
     * @param \Magento\Catalog\Helper\Output              $attributeOutput
     */
    public function __construct(
        ProductInterface $product,
        StoreManagerInterface $storeManager,
        \Magento\Review\Model\Review\SummaryFactory $reviewSummaryFactory,
        \Magento\Catalog\Helper\Output $attributeOutput
    ) {
        $this->product = $product;
        $this->storeManager = $storeManager;
        $this->reviewSummaryFactory = $reviewSummaryFactory;
        parent::__construct($attributeOutput);
    }

    /**
     * Get 'aggregateRating' for product structured data
     *
     * @param  array  $dataMap [description]
     * @return array
     */
    public function get(array $dataMap = [])
    {
        $store = $this->storeManager->getStore();
        if (!$store->getConfig('richsnippets/product/add_reviews_data')) {
            // Do not add reviews data to structured data block

            return [];
        }

        if (!$this->product->getId()) {
            throw new NotFoundException(__('Product not found.'));
        }

        $summaryData = $this->reviewSummaryFactory->create()
            ->setStoreId($this->product->getStoreId())
            ->load($this->product->getId());
        $collectedData = [
            '@type' => 'AggregateRating',
            'bestRating' => '100',
            'worstRating' => '0',
            'ratingValue' => $summaryData->getRatingSummary(),
            'reviewCount' => $summaryData->getReviewsCount(),
            'ratingCount' => $summaryData->getReviewsCount()
        ];
        $data = $this->buildAttributeBasedData($dataMap, $this->product);
        $data = $data + $collectedData;

        if ((int)$data['ratingCount'] > 0 && $data['ratingValue'] > 0) {
            return $data;
        }

        return [];
    }
}
