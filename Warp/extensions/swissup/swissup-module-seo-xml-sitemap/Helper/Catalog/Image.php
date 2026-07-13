<?php

namespace Swissup\SeoXmlSitemap\Helper\Catalog;

class Image extends \Magento\Catalog\Helper\Image
{
    /**
     * {@inheritdoc}
     */
    public function init($product, $imageId, $attributes = [])
    {
        // force to imageId to get same URL as on storefront
        // benefits:
        // 1. sitemap has same iamges as store front
        // 2. reduce number of generated images
        $imageId = 'product_page_image_large_no_frame';
        return parent::init($product, $imageId, $attributes);
    }
}
