<?php

namespace Swissup\SeoCanonical\Model;

use Magento\Email\Model\AbstractTemplate;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Framework\Api\SearchCriteriaBuilder;

class UrlMaker
{
    /**
     * @var Emulation
     */
    private $emulation;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var storeManager
     */
    private $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepo;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepo;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepo;

    /**
     * @var UrlMaker\CategoryUrl
     */
    private $categoryUrl;

    /**
     * @var UrlMaker\ProductUrl
     */
    private $productUrl;

    /**
     * @param Emulation                   $emulation
     * @param SearchCriteriaBuilder       $searchCriteriaBuilder
     * @param StoreManagerInterface       $storeManager
     * @param ProductRepositoryInterface  $productRepo
     * @param CategoryRepositoryInterface $categoryRepo
     * @param PageRepositoryInterface     $pageRepo
     * @param UrlMaker\ProductUrl         $productUrl
     */
    public function __construct(
        Emulation $emulation,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepo,
        CategoryRepositoryInterface $categoryRepo,
        PageRepositoryInterface $pageRepo,
        UrlMaker\CategoryUrl $categoryUrl,
        UrlMaker\ProductUrl $productUrl
    ) {
        $this->emulation = $emulation;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->productRepo = $productRepo;
        $this->categoryRepo = $categoryRepo;
        $this->pageRepo = $pageRepo;
        $this->categoryUrl = $categoryUrl;
        $this->productUrl = $productUrl;
    }

    /**
     * @param  string|int $storeId  [description]
     * @param  callable   $callback [description]
     * @param  array      $params   [description]
     * @return mixed
     * @throws \Exception
     */
    public function emulateStore($storeId, $callback, $params = [])
    {
        $this->emulation->startEnvironmentEmulation(
            $storeId,
            AbstractTemplate::DEFAULT_DESIGN_AREA,
            true
        );
        try {
            $result = call_user_func_array($callback, $params);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            $this->emulation->stopEnvironmentEmulation();
        }

        return $result;
    }

    /**
     * @param  ProductInterface $product
     * @param  string|int       $storeId
     * @return string
     */
    public function getProductUrl(
        ProductInterface $product,
        $storeId = null
    ) {
        if (!$storeId) {
            return $this->productUrl->getUrl($product);
        }

        try {
            $product = $this->productRepo->getById($product->getId(), false, $storeId);

            return $this->emulateStore($storeId, [$this->productUrl, 'getUrl'], [$product]);
        } catch (\Exception $e) {
            // throw $e;

            return '';
        }
    }

    /**
     * @param  CategoryInterface $category
     * @param  string|int        $storeId
     * @return string
     */
    public function getCategoryUrl(
        CategoryInterface $category,
        $storeId = null
    ) {
        if (!$storeId) {
            return $this->categoryUrl->getUrl($category);
        }

        try {
            $category = $this->categoryRepo->get($category->getId(), $storeId);
            $url = $this->emulateStore(
                $storeId,
                [$this->categoryUrl, 'getUrl'],
                [$category]
            );
        } catch (\Exception $e) {
            // throw $e;

            return '';
        }

        $store = $this->storeManager->getStore($storeId);
        $currentStore = $this->storeManager->getStore();

        return str_replace(
            $currentStore->getBaseUrl(),
            $store->getBaseUrl(),
            $url
        );
    }

    /**
     * @param  PageInterface $page
     * @param  string|int    $storeId
     * @return string
     */
    public function getCmsPageUrl(
        PageInterface $page,
        $storeId = null
    ) {
        if (!$storeId) {
            return $this->_getCmsPageUrl($page);
        }

        $criteria = $this->searchCriteriaBuilder
            ->addFilter('identifier', $page->getIdentifier())
            ->addFilter('store_id', $storeId)
            ->create();
        $pages = $this->pageRepo->getList($criteria)->getItems();
        $forcedPage = $pages ? reset($pages) : null;

        return $pages ?
            $this->emulateStore($storeId, [$this, '_getCmsPageUrl'], [reset($pages)]) :
            '';
    }

    /**
     * @param  PageInterface $page
     * @return string
     */
    private function _getCmsPageUrl(PageInterface $page)
    {
        $currentStore = $this->storeManager->getStore();

        return $currentStore->getUrl(null,
            ['_direct' => $page->getIdentifier()]
        );
    }
}
