<?php

namespace Swissup\RichSnippets\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\ObjectManagerInterface;

class DataSnippetFactory
{
    private ObjectManagerInterface $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    public function create(
        string $class,
        ProductInterface $product
    ): DataSnippetInterface {
        return $this->objectManager->create($class, ['product' => $product]);
    }

}
