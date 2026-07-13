<?php

namespace Swissup\SeoImages\Plugin\Service;

class ImageResize
{
    /**
     * @var \Swissup\SeoImages\Helper\Data
     */
    private $helper;

    /**
     * @param \Swissup\SeoImages\Helper\Data         $helper
     */
    public function __construct(
        \Swissup\SeoImages\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Before plugin to replace file key with target file. It is enoght to
     * generate image with requested name.
     * \Swissup\SeoImages\Model\Product\Media\Config will do the rest.
     *
     * @param  \Magento\MediaStorage\Service\ImageResize $subject
     * @param  string                                    $originalImageName
     * @return string
     */
    public function beforeResizeFromImageName(
        \Magento\MediaStorage\Service\ImageResize $subject,
        $originalImageName
    ) {
        if (!$this->helper->isEnabled()) {
            return null;
        }

        $seoImage = $this->helper
            ->getSeoImage('file_key', urldecode($originalImageName));

        return $seoImage->getTargetFile() ? [$seoImage->getTargetFile()] : null;
    }
}
