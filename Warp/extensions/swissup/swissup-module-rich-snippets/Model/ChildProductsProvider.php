<?php

namespace Swissup\RichSnippets\Model;

use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ResourceConfigurable;

class ChildProductsProvider
{
    /**
     * @var \Magento\Framework\App\View
     */
    private $appView;

    /**
     * @var ResourceConfigurable
     */
    private $resourceProductConfigurable;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param \Magento\Framework\App\View $appView
     * @param ResourceConfigurable        $resourceProductConfigurable
     * @param ProductRepository           $productRepository
     * @param SearchCriteriaBuilder       $searchCriteriaBuilder
     */
    public function __construct(
        \Magento\Framework\App\View $appView,
        ResourceConfigurable $resourceProductConfigurable,
        ProductRepository $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->appView = $appView;
        $this->resourceProductConfigurable = $resourceProductConfigurable;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param  ProductInterface $parentProduct
     * @return array
     */
    public function getChildren(ProductInterface $parentProduct): array
    {
        if ('configurable' === $parentProduct->getTypeId()) {
            return $this->getFromLayoutBlock($parentProduct) ?:
                $this->readFromDB($parentProduct);
        }

        return [];
    }

    /**
     * @param  ProductInterface $parentProduct
     * @return array
     */
    private function getFromLayoutBlock(ProductInterface $parentProduct): array
    {
        $layout = $this->appView->getLayout();
        $block = $layout->getBlock('product.info.options.swatches') ?:
            $layout->getBlock('product.info.options.configurable');
        if (!$block || $block->getProduct()->getId() != $parentProduct->getId()) {
            return [];
        }

        return $block->getAllowProducts();
    }

    /**
     * @param  ProductInterface $parentProduct
     * @return array
     */
    private function readFromDB(ProductInterface $parentProduct): array
    {
        $groups = $this->resourceProductConfigurable->getChildrenIds($parentProduct->getId());
        $ids = [];
        foreach ($groups as $children) {
            $ids = array_merge($ids, $children);
        }

        if (empty($ids)) {
            return [];
        }

        $criteria = $this->searchCriteriaBuilder
            ->addFilter('entity_id', implode(',', $ids), 'in')
            ->create();

        return $this->productRepository->getList($criteria)->getItems();
    }
}
