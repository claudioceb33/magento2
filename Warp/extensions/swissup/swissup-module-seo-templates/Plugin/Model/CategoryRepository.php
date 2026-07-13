<?php

namespace Swissup\SeoTemplates\Plugin\Model;

use Swissup\SeoTemplates\Model\Template;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;

class CategoryRepository extends AbstractPlugin
{
    /**
     * After plugin.
     *
     * @param  CategoryRepositoryInterface $subject
     * @param  CategoryInterface           $result
     * @return CategoryInterface
     */
    public function afterGet(
        CategoryRepositoryInterface $subject,
        CategoryInterface $result
    ) {
        $category = $result;
        $currentCategory = $this->getCurrentCategory();
        if ($currentCategory
            && $currentCategory->getId() !== $category->getId()
            // When there is current category in registry
            // and its ID is not the same as category loaded
            // then prevent swissup metadata from loading
        ) {
            $category->setData('swissup_metadata_updated', true);
        }

        $actionName = $this->helper->getRequest()->getFullActionName();
        // Update category metadata only at Catalog Category View page.
        if ($actionName === 'catalog_category_view'
            && $this->helper->isEnabled()
            && !$category->getData('swissup_metadata_updated')
        ) {
            $this->updateMetadata($category);
            $this->optimizeMetadata($category);
            $category->setData('swissup_metadata_updated', true);
        }

        return $category;
    }

    /**
     * @return CategoryInterface|null
     */
    private function getCurrentCategory()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $registry = $objectManager->get(\Magento\Framework\Registry::class);
        $category = $registry->registry('current_category');

        return $category;
    }
}
