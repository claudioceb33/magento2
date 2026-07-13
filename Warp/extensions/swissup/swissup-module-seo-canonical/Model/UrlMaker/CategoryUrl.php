<?php

namespace Swissup\SeoCanonical\Model\UrlMaker;

use Magento\Framework\DataObject;
use Magento\Catalog\Api\Data\CategoryInterface;
use Swissup\SeoCanonical\Setup\Patch\Data\CreateCanonicalAttribute as CategoryPatch;

class CategoryUrl extends AbstractUrl
{
    public function getUrl(DataObject $entity): string
    {
        $category = $entity;
        $canonicalUrlAttribute = CategoryPatch::ATTRIBUTE_NAME;
        $hasAttribute = $canonicalUrlAttribute ?
            !!$category->getData($canonicalUrlAttribute) :
            false;

        return $hasAttribute ?
            $this->getUrlFromAttribute($category, $canonicalUrlAttribute) :
            $this->_getCategoryUrl($category);
    }

    private function _getCategoryUrl(CategoryInterface $category): string
    {
        return $category
            ? $category->getUrl()
            : '';
    }
}
