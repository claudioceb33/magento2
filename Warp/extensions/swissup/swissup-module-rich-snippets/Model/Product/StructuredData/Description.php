<?php

namespace Swissup\RichSnippets\Model\Product\StructuredData;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NotFoundException;
use Swissup\RichSnippets\Model\DataSnippetInterface;

class Description implements DataSnippetInterface
{
    /**
     * @var ProductInterface
     */
    protected $product;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @param ProductInterface                        $product
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     */
    public function __construct(
        ProductInterface $product,
        \Magento\Framework\Filter\FilterManager $filterManager
    ) {
        $this->product = $product;
        $this->filterManager = $filterManager;
    }

    /**
     * Get 'description' for product structured data
     *
     * @return string
     */
    public function get()
    {
        if (!$this->product->getId()) {
            throw new NotFoundException(__('Product not found.'));
        }

        $description = $this->getDescription($this->product->getShortDescription());
        if (empty($description)) {
            $description = $this->getDescription($this->product->getDescription());
            $description = $this->filterManager->truncate(
                $description,
                ['length' => 200, 'breakWords' => false, 'etc' => '...']
            );
        }

        // make string into one line
        $description = str_replace(["\r\n", "\r","\n"], ' ', $description);

        return $description;
    }

    private function getDescription($html): string
    {
        if (!$html) {
            return '';
        }

        return $this->filterManager->stripTags($html, [
            'allowableTags' => null,
            'escape' => false,
        ]);
    }
}
