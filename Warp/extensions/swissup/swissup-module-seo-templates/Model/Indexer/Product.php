<?php

namespace Swissup\SeoTemplates\Model\Indexer;

class Product extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return \Swissup\SeoTemplates\Model\Template::ENTITY_TYPE_PRODUCT;
    }
}
