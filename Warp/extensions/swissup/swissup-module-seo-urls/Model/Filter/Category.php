<?php

namespace Swissup\SeoUrls\Model\Filter;

use Swissup\SeoUrls\Model;

class Category extends AbstractFilter
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Magento\Catalog\Api\Data\CategoryInterface
     */
    protected $currentCategory;
    /**
     * @var integer
     */
    private $iterationCounter = 0;

    /**
     * @var Model\Category
     */
    protected $seoCategory;

    /**
     * @var Model\ResourceModel\Category\View
     */
    protected $categoryView;

    /**
     * @param \Magento\Framework\Registry       $registry
     * @param Model\Category                    $seoCategory
     * @param Model\ResourceModel\Category\View $categoryView
     * @param \Swissup\SeoUrls\Helper\Data      $helper
     * @param array                             $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        Model\Category $seoCategory,
        Model\ResourceModel\Category\View $categoryView,
        \Swissup\SeoUrls\Helper\Data $helper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->seoCategory = $seoCategory;
        $this->categoryView = $categoryView;
        parent::__construct($helper, $data);
    }

    /**
     * Get category for layered filter
     *
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     */
    public function getCategory()
    {
        if ($this->registry->registry('current_category_filter')) {
            return $this->registry->registry('current_category_filter');
        }

        return $this->getCurrentCategory();
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->helper->getPredefinedFilterLabel('category_filter');
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        $category = $this->getCategory();
        if ((null !== $category)
            && !$this->hasData("options_{$category->getId()}")
        ) {
            $children = $this->getCategory()->getChildrenCategories();
            $this->categoryView->preloadData(
                array_merge(
                    $this->helper->getIdsFromCategories($children),
                    [$category->getId(), $category->getParentId()]
                )
            );
            $options = [];
            foreach ($children as $child) {
                if (!$child->getIsActive()) {
                    continue;
                }

                $label = $this->seoCategory->getStoreLabel($child);
                if (in_array($label, $options)) {
                    // this should not occur - poor category naming
                    // concatenate value to duplicated label
                    $label .= '-' . $child->getId();
                }

                // option prefix is path to current category filter from current category
                $options[$child->getId()] = $this->getOptionPrefix() . $label;
            }
            // add current category filter option
            $options[$this->getCategory()->getId()] = rtrim($this->getOptionPrefix(), '-');
            // add parent of current category filter option
            $options[$this->getCategory()->getParentId()] = rtrim(
                $this->generateLabelForCategory(
                    '',
                    $this->getCategory()->getParentCategory()
                ),
                '-'
            );

            $this->setData("options_{$category->getId()}", $options);
        }

        return $this->getData("options_{$category->getId()}");
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return 0;
    }

    /**
     * Get prefix for filter options
     *
     * @return string
     */
    protected function getOptionPrefix()
    {
        $category = $this->getCategory();
        if (!$this->hasData("option_prefix_{$category->getId()}")) {
            $this->setData(
                "option_prefix_{$category->getId()}",
                $this->generateLabelForCategory()
            );
        }

        return $this->getData("option_prefix_{$category->getId()}");
    }

    /**
     * Generate label for category
     *
     * @param string $sufix
     * @param \Magento\Catalog\Api\Data\CategoryInterface $category
     * @return string
     * @throws \Exception
     */
    private function generateLabelForCategory($sufix = '', $category = null)
    {
        if ($category === null) {
            $category = $this->getCategory();
        }

        if ($category->getId() == $this->getCurrentCategory()->getId()
            || !$category->getParentId()
        ) {
            $this->iterationCounter = 0;
            return $sufix;
        } else {
            $sufix = $this->seoCategory->getStoreLabel($category)
                . '-'
                . $sufix
                ;
            if ($this->iterationCounter++ > 100) {
                throw new \Exception(__('Infinite recursion call or category tree is too deep.'));
            } else {
                return $this->generateLabelForCategory($sufix, $category->getParentCategory());
            }
        }
    }

    /**
     * Get curent category if it is set otherwise get root category
     *
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     */
    protected function getCurrentCategory()
    {

        if (!isset($this->currentCategory)) {
            if ($this->registry->registry('current_category')) {
                $this->currentCategory = $this->registry->registry('current_category');
            } else {
                $this->currentCategory = $this->helper->getRootCategory();
            }
        }

        return $this->currentCategory;
    }
}
