<?php

namespace Swissup\SeoImages\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

class ProductResolver
{
    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var IndexFactory
     */
    private $indexFactory;

    /**
     * @var ResourceModel\Product\Gallery
     */
    private $resourceGallery;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ProductMetadataInterface
     */
    private $magentoMetadata;

    /**
     * @param IndexFactory                  $indexFactory
     * @param ResourceModel\Product\Gallery $resourceGallery
     * @param ProductRepositoryInterface    $productRepository
     * @param SearchCriteriaBuilder         $searchCriteriaBuilder
     * @param ProductMetadataInterface      $magentoMetadata
     */
    public function __construct(
        IndexFactory $indexFactory,
        ResourceModel\Product\Gallery $resourceGallery,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductMetadataInterface $magentoMetadata
    ) {
        $this->indexFactory = $indexFactory;
        $this->resourceGallery = $resourceGallery;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->magentoMetadata = $magentoMetadata;
    }

    /**
     * Find product by gallery image assigned to it.
     *
     * @param  string                $galleryImage
     * @return ProductInterface|null
     */
    public function getByGalleryImage($galleryImage)
    {
        if (!isset($this->cache[$galleryImage])) {
            $product = $this->_getByGalleryImage($galleryImage);
            if ($product) {
                $this->cache[$galleryImage] = $product;
                $gallery = $product->getMediaGallery('images') ?: [];
                foreach ($product->getMediaGallery('images') as $image) {
                    $this->cache[$image['file']] = $product;
                }
            } else {
                $this->cache[$galleryImage] = null;
            }
        }

        return $this->cache[$galleryImage];
    }

    private function _getByGalleryImage($galleryImage)
    {
        $index = $this->indexFactory->create()->load($galleryImage, 'file');
        if ($index->getId()) {
            return $this->getFirstVisibleProduct(
                [$index->getEntityId()],
                'entity_id' // $index is my own not Magento so I'm sure it is `entity_id`
            );
        }

        // Product image not found in index table.
        $product = $this->fallbackFindProduct($galleryImage);

        if ($product) {
            // Update index manually. Looks like something wrong with indexes.
            $index->executeRow($product->getId());
        }

        return $product;
    }

    /**
     * Fallback to find product manually. Because there is no relation in indexes.
     *
     * @param  string                $galleryImage
     * @return ProductInterface|null
     */
    private function fallbackFindProduct($galleryImage)
    {
        $ids = $this->resourceGallery->getProductIds($galleryImage);
        if (empty($ids)) {
            return null;
        }

        return $this->getFirstVisibleProduct(
            $ids,
            $this->getFilterFiledName()
        );
    }

    /**
     * @param  array  $productIds
     * @param  string $fieldName
     * @return ProductInterface|null
     */
    private function getFirstVisibleProduct($productIds, $fieldName)
    {
        $criteria = $this->searchCriteriaBuilder
            ->addFilter(
                $this->getFilterFiledName(),
                implode(',', $productIds),
                'in'
            )->create();

        $products = $this->productRepository->getList($criteria)->getItems();
        foreach ($products as $product) {
            if ($product->isVisibleInSiteVisibility()) {
                break;
            }
        }

        return $product ?? null;
    }

    /**
     * @return string
     */
    private function getFilterFiledName()
    {
        return in_array($this->magentoMetadata->getEdition(), ['Enterprise', 'B2B'])
            ? 'row_id'
            : 'entity_id';
    }

    /**
     * @param  Collection $collection
     * @return array
     */
    public function preloadFromCollection(Collection $collection)
    {
        $preloadGalleryImages = [];
        $imagesCollection = $this->indexFactory->create()->getCollection();
        $imagesCollection->addFieldToFilter('entity_id', [
            'in' => $collection->getColumnValues('entity_id')
        ]);
        foreach ($collection as $product) {
            $gallery = $imagesCollection->getItemsByColumnValue(
                'entity_id',
                $product->getId()
            );
            foreach ($gallery as $image) {
                $preloadGalleryImages[] = $image['file'];
                $this->cache[$image['file']] = $product;
            }
        }

        return $preloadGalleryImages;
    }
}
