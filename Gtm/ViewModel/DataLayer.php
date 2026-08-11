<?php
namespace Ceb\Gtm\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Registry;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Ceb\Gtm\Helper\Data as GtmHelper;
use Ceb\Gtm\ViewModel\ProductList;

class DataLayer implements ArgumentInterface
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var GtmHelper
     */
    protected $gtmHelper;

    /**
     * @var ProductCollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ProductList
     */
    protected $productList;

    public function __construct(
        Registry $registry,
        CheckoutSession $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        ProductCollectionFactory $collectionFactory,
        GtmHelper $gtmHelper,
        ProductList $productList
    ) {
        $this->registry = $registry;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->collectionFactory = $collectionFactory;
        $this->gtmHelper = $gtmHelper;
        $this->productList = $productList;
    }

    public function getCurrentProduct(): ?ProductInterface
    {
        return $this->registry->registry('current_product');
    }

    public function getViewItemData(): ?array
    {
        $product = $this->getCurrentProduct();
        if (!$product) {
            return null;
        }

        $variant = null;
        if ($product->getTypeId() == 'configurable') {
            $childrens = $product->getTypeInstance()->getUsedProducts($product);
            foreach ($childrens as $child) {
                if ($child->isSaleable()) {
                    $product = $child;
                    $variant = $child->getSku();
                    break;
                }
            }
        }

        $item = [
            'item_id' => $product->getSku(),
            'item_name' => $product->getName(),
            'price' => (float) $product->getFinalPrice(),
            'quantity' => 1
        ];

        $brand = $this->gtmHelper->getProductBrand($product);
        if ($brand) {
            $item['item_brand'] = $brand;
        }

        $category = $this->gtmHelper->getProductCategory($product);
        if ($category) {
            $item['item_category'] = $category;
        }

        if($variant) {
            $item['item_variant'] = $variant;
        }

        $result = [
            'event' => 'view_item',
            'ecommerce' => [
                'items' => [$item]
            ]
        ];

        return $result;
    }

    public function getPurchaseData(): ?array
    {
        $lastOrderId = $this->checkoutSession->getLastOrderId();

        if (!$lastOrderId) {
            return null;
        }

        try {
            $order = $this->orderRepository->get($lastOrderId);
        } catch (\Exception $e) {
            return null;
        }

        $transactionId = trim((string)$order->getIncrementId());

        if ($transactionId === '') {
            return null;
        }

        $items = [];

        foreach ($order->getAllVisibleItems() as $item) {
            $product = $item->getProduct();

            $itemData = [
                'item_id' => (string)$item->getSku(),
                'item_name' => (string)$item->getName(),
                'price' => (float)$item->getPrice(),
                'quantity' => (int)$item->getQtyOrdered()
            ];

            if ($product) {
                $brand = $this->gtmHelper->getProductBrand($product);
                if ($brand) {
                    $itemData['item_brand'] = (string)$brand;
                }

                $category = $this->gtmHelper->getProductCategory($product);
                if ($category) {
                    $itemData['item_category'] = (string)$category;
                }
            }

            $items[] = $itemData;
        }

        if (empty($items)) {
            return null;
        }

        return [
            'event' => 'purchase',
            'event_source' => 'browser',
            'gtm_source' => 'magento_frontend',
            'ecommerce' => [
                'transaction_id' => $transactionId,
                'order_increment_id' => $transactionId,
                'value' => (float)$order->getGrandTotal(),
                'currency' => (string)$order->getOrderCurrencyCode(),
                'tax' => (float)$order->getTaxAmount(),
                'shipping' => (float)$order->getShippingAmount(),
                'items' => $items
            ]
        ];
    }

    public function getBeginCheckoutData(): ?array
    {
        $quote = $this->checkoutSession->getQuote();
        if (!$quote || !$quote->hasItems()) {
            return null;
        }

        $items = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $product = $item->getProduct();

            if ($product->getPrice() < 1) continue;
            
            $itemData = [
                'item_id' => $item->getSku(),
                'item_name' => $product->getName(),
                'price' => (float) $product->getPrice(),
                'quantity' => (int) $item->getQty()
            ];

            if ($product) {
                $brand = $this->gtmHelper->getProductBrand($product);
                if ($brand) {
                    $itemData['item_brand'] = $brand;
                }
                $category = $this->gtmHelper->getProductCategory($product);
                if ($category) {
                    $itemData['item_category'] = $category;
                }
            }

            $items[] = $itemData;
        }

        return [
            'event' => 'begin_checkout',
            'ecommerce' => [
                'currency' => $quote->getQuoteCurrencyCode(),
                'value' => (float) $quote->getGrandTotal(),
                'items' => $items
            ]
        ];
    }

    public function getViewItemListData(): ?array
    {
        $items = $this->productList->getViewItemListItems(20);
        if (empty($items)) {
            return null;
        }

        $itemListName = $this->resolveItemListName();

        return [
            'event' => 'view_item_list_simple',
            'ecommerce' => [
                'item_list_name' => $itemListName,
                'items' => $items
            ]
        ];
    }

    public function getPageViewData(): array
    {
        return [
            'event' => 'page_view',
            'page_type' => 'cms',
            'page_url' => '',
            'page_name' => 'cms_page'
        ];
    }

    /**
     * @return string
     */
    protected function resolveItemListName(): string
    {
        return $this->productList->getCurrentItemListName();
    }
}
