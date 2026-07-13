<?php

namespace Swissup\SeoUrls\Plugin\Catalog\Model\Layer;

use Magento\Catalog\Model\Layer\FilterList as Subject;
use Swissup\SeoUrls\Model\ResourceModel\Attribute\View as AttributeView;

class FilterList
{
    /**
     * @var \Swissup\SeoUrls\Helper\Data
     */
    private $helper;

    /**
     * @var AttributeView
     */
    private $attributeView;

    /**
     * @param \Swissup\SeoUrls\Helper\Data $helper
     * @param AttributeView                $attributeView
     */
    public function __construct(
        \Swissup\SeoUrls\Helper\Data $helper,
        AttributeView $attributeView
    ) {
        $this->helper = $helper;
        $this->attributeView = $attributeView;
    }

    /**
     * @param  Subject $subject
     * @param  array   $result
     * @return array
     */
    public function afterGetFilters(
        Subject $subject,
        array $result
    ) {
        if ($this->helper->isSeoUrlsEnabled()) {
            $attributeIds = [];
            foreach ($result as $filter) {
                if ($filter->hasAttributeModel()) {
                    $attributeIds[] = $filter->getAttributeModel()->getId();
                }
            }

            if ($attributeIds) {
                $this->attributeView->preloadData($attributeIds);
            }
        }

        return $result;
    }
}
