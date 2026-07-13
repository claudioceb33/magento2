<?php

namespace Swissup\SeoTemplates\Plugin\Model;

use Swissup\SeoTemplates\Model\SeodataBuilder;
use Swissup\SeoTemplates\Model\Template;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class ProductRepository extends AbstractPlugin
{
    /**
     * After plugin.
     *
     * @param  ProductRepositoryInterface $subject
     * @param  ProductInterface           $result
     * @return ProductInterface
     */
    public function afterGetById(
        ProductRepositoryInterface $subject,
        ProductInterface $result
    ) {
        return $this->assignMetadata($result);
    }

    /**
     * After plugin.
     *
     * @param  ProductRepositoryInterface $subject
     * @param  ProductInterface           $result
     * @return ProductInterface
     */
    public function afterGet(
        ProductRepositoryInterface $subject,
        ProductInterface $result
    ) {
        return $this->assignMetadata($result);
    }

    /**
     * Assign generated metadata to product if module enabled.
     *
     * @param  ProductInterface $product
     * @return ProductInterface
     */
    private function assignMetadata(ProductInterface $product)
    {
        $actionName = $this->helper->getRequest()->getFullActionName();
        // Update category metadata only at Catalog Product View page.
        if ($actionName === 'catalog_product_view'
            && $this->helper->isEnabled()
            && !$product->getData('swissup_metadata_updated')
        ) {
            $this->updateMetadata($product);
            // Workaround to set meta keywords for product.
            // Check code at \Magento\Catalog\Helper\Product\View::preparePageMetadata#L120
            // Category works normal!
            if ($product->hasMetaKeywords()) {
                $product->setData('meta_keyword', $product->getMetaKeywords());
            }

            $this->optimizeMetadata($product);
            $this->updateMediaGallery($product, Template::ENTITY_TYPE_PRODUCT);
            $product->setData('swissup_metadata_updated', true);
        }

        return $product;
    }

    /**
     * @param  ProductInterface $product
     */
    protected function updateMediaGallery(ProductInterface $product)
    {
        $mediaGallery = $product->getMediaGallery();
        if (!isset($mediaGallery['images'])) {
            return;
        }

        $images = &$mediaGallery['images'];
        if (!is_array($images)) {
            return;
        }

        $imageAlt = $this->seodataBuilder->getValidatedByKey('image_alt', $product);
        foreach ($images as &$image) {
            if ($imageAlt                        // there is generated image alt
                && (
                    empty($image['label'])       // AND image label is empty
                    || $this->helper->isForced() // or force generated data enabled
                )
            ) {
                $image['label'] = $imageAlt;
            }
        }

        $product->setMediaGallery($mediaGallery);
    }
}
