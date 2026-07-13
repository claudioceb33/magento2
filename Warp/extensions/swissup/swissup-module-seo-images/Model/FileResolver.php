<?php

namespace Swissup\SeoImages\Model;

class FileResolver
{
    /**
     * @var NameGenerator
     */
    private $fileName;

    /**
     * @var \Swissup\SeoImages\Helper\Data
     */
    private $helper;

    /**
     * @var ProductResolver
     */
    private $productResolver;

    /**
     * @var string[]
     */
    private $targetFiles = [];

    /**
     * @param NameGenerator                  $fileName
     * @param \Swissup\SeoImages\Helper\Data $helper
     * @param ProductResolver                $productResolver
     */
    public function __construct(
        NameGenerator $fileName,
        \Swissup\SeoImages\Helper\Data $helper,
        ProductResolver $productResolver
    ) {
        $this->fileName = $fileName;
        $this->helper = $helper;
        $this->productResolver = $productResolver;
    }

    public function getTargetFile($originalFile)
    {
        if (!isset($this->targetFiles[$originalFile])) {
            if ($this->helper->isProduction()) {
                $image = $this->helper->getCachedSeoImage($originalFile);
                $this->targetFiles[$originalFile] = $image->getTargetFile()
                    ? $image->getTargetFile()
                    : $originalFile;
            } else {
                $product = $this->productResolver->getByGalleryImage($originalFile);
                $this->targetFiles[$originalFile] = $product
                    ? $this->fileName->generate($product,$originalFile)
                    : '';
            }
        }

        return $this->targetFiles[$originalFile];
    }

    /**
     * @param string $originalFile
     * @param string $targetFile
     */
    public function setTargetFile($originalFile, $targetFile)
    {
        $this->targetFiles[$originalFile] = $targetFile;

        return $this;
    }
}
