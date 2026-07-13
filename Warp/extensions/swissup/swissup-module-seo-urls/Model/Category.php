<?php

namespace Swissup\SeoUrls\Model;

use Swissup\SeoUrls\Model\ResourceModel\Category\View as CategoryView;

class Category
{
    /**
     * @var \Swissup\SeoUrls\Helper\Data
     */
    private $helper;

    /**
     * @var CategoryView
     */
    private $categoryView;

    /**
     * @param \Swissup\SeoUrls\Helper\Data $helper
     * @param CategoryView                 $categoryAction
     */
    public function __construct(
        \Swissup\SeoUrls\Helper\Data $helper,
        CategoryView $categoryView
    ) {
        $this->helper = $helper;
        $this->categoryView = $categoryView;
    }

    /**
     * Get in-URL label for category
     *
     * @param  \Magento\Framework\DataObject $category
     * @return string|null
     */
    public function getInUrlLabel(\Magento\Framework\DataObject $category)
    {
        $storeId = $this->helper->getCurrentStore()->getId();
        $labels = $this->categoryView->getInUrlLabels($category);
        return isset($labels[$storeId])
            ? $labels[$storeId]['value']
            : (
                isset($labels[0])
                    ? $labels[0]['value']
                    : null
                );
    }

    /**
     * Get original store label of category converted into seo-friendly string
     *
     * @param  \Magento\Framework\DataObject $category
     * @return string
     */
    public function getFallbackLabel(\Magento\Framework\DataObject $category)
    {
        $label = $category->getName();
        return $this->helper->getSeoFriendlyString($label);
    }

    /**
     * Get in-URL label for attribuet with fallback to converted orignal label
     *
     * @param  \Magento\Framework\DataObject $category
     * @return string
     */
    public function getStoreLabel(\Magento\Framework\DataObject $category)
    {
        $label = $this->getInUrlLabel($category);
        if (!$label) {
            $label = $this->getFallbackLabel($category);
        }

        return $label;
    }
}
