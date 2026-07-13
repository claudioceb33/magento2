<?php

namespace Swissup\SeoTemplates\Block\Adminhtml\Metadata;

class Category extends AbstractBlock
{
    /**
     * {@inheritdoc}
     */
    public function getCurrentEntity()
    {
        return $this->registry->registry('current_category');
    }
}
