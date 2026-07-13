<?php

namespace Swissup\SeoCanonical\Model;

use Magento\Framework\Exception\NoSuchEntityException;

class ParentProduct
{
    const XML_PATH_PARENT_TYPES = 'swissup_seocanonical/product/parent_types';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var array
     */
    protected $parentFinder = [];

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Api\ProductRepositoryInterface    $productRepository
     * @param array                                              $parentFinder
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        array $parentFinder
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
        $this->parentFinder = $parentFinder;
    }

    /**
     * Get info about parent product by child product
     *
     * @param  \Magento\Catalog\Api\Data\ProductInterface $child
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function get($child)
    {
        $allowedTypes = $this->getAllowedParentTypes();
        foreach ($this->parentFinder as $type => $finder) {
            if (!in_array($type, $allowedTypes)) {
                continue;
            }

            $parentIds = $finder->getParentIds($child->getId());
            if ($parent = $this->getFisrtAvailableProduct($parentIds)) {
                return $parent;
            }
        }

        return null;
    }

    /**
     * @param  array  $productIds
     * @return [type]
     */
    protected function getFisrtAvailableProduct(array $productIds)
    {
        foreach ($productIds as $productId) {
            try {
                $product = $this->productRepository->getById($productId);
                if ($product->isVisibleInSiteVisibility()
                    && !$product->isDisabled()
                ) {
                    return $product;
                }
            } catch (NoSuchEntityException $e) {
                continue;
            }
        }

        return null;
    }

    /**
     * @return array
     */
    public function getAllowedParentTypes()
    {
        $allowedTypes = $this->scopeConfig->getValue(
            self::XML_PATH_PARENT_TYPES,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return explode(',', $allowedTypes);
    }
}
