<?php

namespace Swissup\SeoTemplates\Block\Adminhtml\Metadata;

class Product extends AbstractBlock
{
    /**
     * {@inheritdoc}
     */
    public function getCurrentEntity()
    {
        return $this->registry->registry('current_product');
    }
}
