<?php

namespace Swissup\RichSnippets\Model\Config\Source;

class ProductDropdownAttributes extends ProductAttributes
{
    /**
     * {@inheritdoc}
     */
    protected function getSearchCriteria()
    {
        return $this->searchCriteriaBuilder
            ->addFilter('frontend_input', 'select')
            ->create();
    }
}
