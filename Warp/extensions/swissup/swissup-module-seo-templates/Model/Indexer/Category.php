<?php

namespace Swissup\SeoTemplates\Model\Indexer;

class Category extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return \Swissup\SeoTemplates\Model\Template::ENTITY_TYPE_CATEGORY;
    }
}
