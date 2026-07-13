<?php

namespace Swissup\RichSnippets\Model\Product\StructuredData;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NotFoundException;
use Swissup\RichSnippets\Model\DataSnippetInterface;
use Swissup\RichSnippets\Model\Product\Config;

class Image implements DataSnippetInterface
{
    /**
     * @var ProductInterface
     */
    protected $product;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    private Config $config;

    /**
     * @param ProductInterface              $product
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param Config                        $config
     */
    public function __construct(
        ProductInterface $product,
        \Magento\Catalog\Helper\Image $imageHelper,
        Config $config
    ) {
        $this->product = $product;
        $this->imageHelper = $imageHelper;
        $this->config = $config;
    }

    /**
     * Get 'image' for product structured data
     *
     * @return string
     */
    public function get()
    {
        if (!$this->product->getId()) {
            throw new NotFoundException(__('Product not found.'));
        }

        $imageId = $this->config->getImageId($this->product->getStoreId());

        return $this->imageHelper
            ->init($this->product, $imageId)
            ->setImageFile($this->product->getImage())
            ->getUrl();
    }
}
