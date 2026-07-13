<?php

namespace Swissup\SeoUrls\Plugin\Catalog\Model\ResourceModel;

use Magento\Store\Model\Store;
use Swissup\SeoUrls\Model\ResourceModel\Category\View as CategoryView;
use Swissup\SeoUrls\Model\ResourceModel\Category\Action as categoryAction;

class Category
{
    /**
     * @var CategoryView
     */
    private $categoryView;

    /**
     * @var categoryAction
     */
    private $categoryAction;

    /**
     * @param CategoryView   $categoryView
     * @param categoryAction $categoryAction
     */
    public function __construct(
        CategoryView $categoryView,
        categoryAction $categoryAction
    ) {
        $this->categoryView = $categoryView;
        $this->categoryAction = $categoryAction;
    }

    /**
     * Before plugin for afterSave method.
     * Save swissup_seourl_label data.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category $subject
     */
    public function beforeAfterSave(
        \Magento\Catalog\Model\ResourceModel\Category $subject,
        \Magento\Catalog\Model\Category $category
    ) {
        // save label
        $labels = [
            $category->getData('store_id') => $category->getData('swissup_seourl_label')
        ];
        $this->categoryAction->updateInUrlLabels($category, $labels);
    }

    /**
     * Append category data with seourl_label and flag to show default checkbox
     *
     * @param  \Magento\Catalog\Model\ResourceModel\Category $subject
     * @param  \Magento\Catalog\Model\Category               $category
     * @return [type]
     */
    public function beforeAfterLoad(
        \Magento\Catalog\Model\ResourceModel\Category $subject,
        \Magento\Catalog\Model\Category $category
    ) {
        $storeId = $category->getStoreId();
        $inUrlLabels = $this->categoryView->getInUrlLabels($category);
        $category->setData(
            'swissup_seourl_label',
            isset($inUrlLabels[$storeId])
                ? $inUrlLabels[$storeId]['value']
                : (
                    isset($inUrlLabels[Store::DEFAULT_STORE_ID])
                        ? $inUrlLabels[Store::DEFAULT_STORE_ID]['value']
                        : ''
                    )
        );
        $category->setData(
            'swissup_seourl_label_is_default',
            !isset($inUrlLabels[$storeId])
        );
    }
}
