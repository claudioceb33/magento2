<?php

namespace Swissup\RichSnippets\Model\Product\StructuredData;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use Swissup\RichSnippets\Model\DataSnippetInterface;

class Review implements DataSnippetInterface
{
    /**
     * @var ProductInterface
     */
    protected $product;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param ProductInterface                                   $product
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param CollectionFactory                                  $collectionFactory
     */
    public function __construct(
        ProductInterface $product,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CollectionFactory $collectionFactory
    ) {
        $this->product = $product;
        $this->storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get review data
     *
     * @return array
     */
    public function get()
    {
        $store = $this->storeManager->getStore();
        if (!$store->getConfig('richsnippets/product/add_reviews_data')) {
            // Do not add reviews data to structured data block

            return [];
        }

        if (!$this->product->getId()) {
            throw new NotFoundException(__('Product not found.'));
        }

        $collection = $this->collectionFactory->create()->addStoreFilter(
                $this->product->getStoreId()
            )->addStatusFilter(
                \Magento\Review\Model\Review::STATUS_APPROVED
            )->addEntityFilter(
                'product',
                $this->product->getId()
            )->setDateOrder()
            ->setPageSize(1)
            ->addRateVotes();
        // get latest review
        $item = $collection->getFirstItem();
        $votes = [];
        // check code/Magento/Review/view/frontend/templates/product/view/list.phtml
        // to figure out how to handle votes
        if (!$item->getRatingVotes()) {
            return [];
        }

        foreach ($item->getRatingVotes() as $vote) {
            $votes[] = $vote->getPercent();
        }

        $ratingValue = count($votes) ? (array_sum($votes) / count($votes)) : 0;
        if (!$ratingValue) {
            return [];
        }

        return [
            '@type' => 'Review',
            'reviewRating' => [
                '@type' => 'Rating',
                'ratingValue' => $ratingValue,
                'bestRating' => 100,
            ],
            'author' => [
                '@type' => 'Person',
                'name' => $item->getNickname()
            ]
        ];
    }
}
