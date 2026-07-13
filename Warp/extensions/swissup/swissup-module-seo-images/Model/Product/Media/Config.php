<?php

namespace Swissup\SeoImages\Model\Product\Media;

use Magento\Store\Model\StoreManagerInterface;

class Config extends \Magento\Catalog\Model\Product\Media\Config
{
    /**
     * @var \Swissup\SeoImages\Model\EntityFactory
     */
    private $seoImageFactory;

    /**
     * @param \Swissup\SeoImages\Model\EntityFactory $seoImageFactory
     * @param StoreManagerInterface                  $storeManager
     */
    public function __construct(
        \Swissup\SeoImages\Model\EntityFactory $seoImageFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->seoImageFactory = $seoImageFactory;
        parent::__construct($storeManager);
    }

    /**
     * Get path to file in media directory.
     * Use original file name when SEO one requested.
     *
     * {@inheritdoc}
     */
    public function getMediaPath($file)
    {
        $seoImage = $this->seoImageFactory->create()
            ->load(urldecode($file), 'target_file');
        if ($seoImage->getFileKey()) {
            return parent::getMediaPath($seoImage->getOriginalFile());
        }

        return parent::getMediaPath($file);
    }
}
