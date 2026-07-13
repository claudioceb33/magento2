<?php

namespace Swissup\SeoImages\Model;

use Magento\Framework\Exception\RuntimeException;

class NameGenerator
{

    /**
     * @var Filter\Product
     */
    protected $processor;

    /**
     * @var \Swissup\SeoImages\Helper\Data
     */
    protected $helper;

    /**
     * @var array
     */
    protected $processed = [];

    /**
     * @var string[]
     */
    protected $names = [];

    /**
     * @param Filter\Product                 $processor
     * @param \Swissup\SeoImages\Helper\Data $helper
     */
    public function __construct(
        Filter\Product $processor,
        \Swissup\SeoImages\Helper\Data $helper
    ) {

        $this->processor = $processor;
        $this->helper = $helper;
    }

    /**
     * Generate file name for product.
     *
     * @param  \Magento\Framework\DataObject $product
     * @param  string                        $originalFile
     * @return string
     */
    public function generate(
        \Magento\Framework\DataObject $product,
        $originalFile
    ) {
        if (!isset($this->names[$originalFile][$product->getId()])) {
            $file = '';
            if ($name = $this->processTemplate($product)) {
                $i = 0;
                $extension = $this->getFileExtension($originalFile);
                // Figure out new name for SEO image
                do {
                    $file = '/' . $name . ($i ? "-{$i}" : '') . '.' . $extension;
                    $i++;
                    if ($i > 100) {
                        throw new RuntimeException(__('Looks like Magento went into infinit loop... Product ID - %1. Image - %2', $product->getId(), $originalFile));
                    }

                    $seoImage = $this->helper->getSeoImage('target_file', $file);
                } while ($seoImage->getFileKey()
                    && $seoImage->getOriginalFile() !== $originalFile
                );
            }

            $this->names[$originalFile][$product->getId()] = $file;
        }

        return $this->names[$originalFile][$product->getId()];
    }

    /**
     * Get file extension.
     *
     * @param  string $fileName
     * @return string
     */
    public function getFileExtension($fileName)
    {
        return pathinfo($fileName, PATHINFO_EXTENSION);
    }

    /**
     * Process template of file name.
     *
     * @param  \Magento\Framework\DataObject $product
     * @return string
     */
    protected function processTemplate(
        \Magento\Framework\DataObject $product
    ) {
        if (!isset($this->processed[$product->getId()])) {
            $template = $this->helper->getSeoImageNameTemplate();
            $result = $this->processor->setScope($product)->filter($template);
            $this->processed[$product->getId()] = ltrim($result, '/');
        }

        return ltrim($this->processed[$product->getId()]);
    }
}
