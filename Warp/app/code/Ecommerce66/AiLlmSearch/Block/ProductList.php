<?php
declare(strict_types=1);

namespace Ecommerce66\AiLlmSearch\Block;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\ListProduct as MagentoListProduct;
use Magento\Catalog\Helper\Output as OutputHelper;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Url\Helper\Data as UrlHelper;

class ProductList extends MagentoListProduct
{
    /**
     * @var Product[]
     */
    private array $products = [];

    /**
     * @var OutputHelper
     */
    private OutputHelper $outputHelper;

    /**
     * @var PricingHelper
     */
    private PricingHelper $pricingHelper;

    /**
     * @param Context $context
     * @param PostHelper $postDataHelper
     * @param Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param UrlHelper $urlHelper
     * @param OutputHelper $outputHelper
     * @param PricingHelper $pricingHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        UrlHelper $urlHelper,
        OutputHelper $outputHelper,
        PricingHelper $pricingHelper,
        array $data = []
    ) {
        $this->outputHelper = $outputHelper;
        $this->pricingHelper = $pricingHelper;
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
    }

    /**
     * Mark block output as private to avoid caching dynamic form keys/results.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_isScopePrivate = true;
    }

    /**
     * Prepare layout
     * 
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setData('outputHelper', $this->outputHelper);
        return $this;
    }

    /**
     * Set custom products for AI search results
     * 
     * @param Product[] $products
     */
    public function setProducts(array $products): self
    {
        $this->products = $products;
        return $this;
    }

    /**
     * Get products for AI search results
     * Override parent method to return custom products
     * 
     * @return Product[]|\Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getLoadedProductCollection()
    {
        if (!empty($this->products)) {
            return $this->products;
        }
        return parent::getLoadedProductCollection();
    }

    /**
     * Format price
     * 
     * @param float $price
     * @return string
     */
    public function formatPrice(float $price): string
    {
        return $this->pricingHelper->currency($price, true, false);
    }
}
