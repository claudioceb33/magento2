<?php

namespace Swissup\SeoUrls\Helper;

use Magento\Catalog\Model\Layer\Category\FilterableAttributeList;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Swissup\SeoUrls\Model\Filter\FilterFactories;
use Swissup\SeoUrls\Model;

class Filter extends Data implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var
     */
    protected $attributeFiltersList;

    /**
     * @var array
     */
    protected $filters = [];

    /**
     * @var \Swissup\SeoUrls\Model\Filter\FilterFactories
     */
    protected $filterFactories;

    /**
     * @var \Magento\Framework\App\AreaList
     */
    protected $areaList;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var Model\Attribute
     */
    protected $seoAttribute;

    /**
     * @var Model\ResourceModel\Attribute\View
     */
    protected $attributeView;

    /**
     * Constructor
     *
     * @param FilterableAttributeList               filters
     * @param FilterFactories                       $filterFactories
     * @param \Magento\Framework\App\AreaList       $areaList
     * @param \Magento\Framework\App\State          $appState
     * @param Model\Attribute                       $seoAttribute
     * @param Model\ResourceModel\Attribute\View    $attributeView
     * @param Context                               $seoUrlsContext
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Category\FilterableAttributeList $filters,
        FilterFactories $filterFactories,
        \Magento\Framework\App\AreaList $areaList,
        \Magento\Framework\App\State $appState,
        Model\Attribute $seoAttribute,
        Model\ResourceModel\Attribute\View $attributeView,
        Context $seoUrlsContext,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->attributeFiltersList = $filters->getList();
        $this->filterFactories = $filterFactories;
        $this->areaList = $areaList;
        $this->appState = $appState;
        $this->seoAttribute = $seoAttribute;
        $this->attributeView = $attributeView;
        parent::__construct(
            $seoUrlsContext,
            $context
        );
    }

    /**
     * Get Seo filter by name
     *
     * @param  string $name
     * @param  int $categoryId
     * @return Swissup\SeoUrls\Model\Filter\AbstractFilter
     */
    public function getByName($name)
    {
        if (!array_key_exists($name, $this->filters)) {
            // try to find predefined filter: category, stock, rating
            foreach ($this->predefinedFiltersList->getData() as $filterName => $filter) {
                if ($name == $filter->getRequestVar()) {
                    $this->filters[$name] = $this->createSeoFilter(
                        $filterName,
                        $filter->getAttributeCode()
                    );
                    break;
                }
            }

            // its not predefined filter so create regular attribute filter
            if (!isset($this->filters[$name])) {
                $this->filters[$name] = $this->createSeoFilter(
                    'attribute_filter',
                    $name
                );
            }
        }

        return $this->filters[$name];
    }

    /**
     * Find layer filter in string
     */
    public function findFilterInString(string $string): array
    {
        // try to find predefined filter
        $this->loadTranslates();
        $this->preloadAttributeData();
        foreach ($this->predefinedFiltersList->getData() as $filter) {
            $seoLabel = '';
            if ($filter->hasAttributeCode()) {
                $seoLabel = $this->seoAttribute->getInUrlLabel($filter);
            }

            $seoLabel = $seoLabel
                ? $seoLabel
                : $this->getSeoFriendlyString($filter->getStoreLabel());
            if ($seoLabel && strpos($string, $seoLabel) === 0) {
                return [$filter->getRequestVar()];
            }
        }

        // try to find attribute filter
        $name = [];
        $matchedLabel = '';
        foreach ($this->attributeFiltersList as $filter) {
            $seoLabel = $this->seoAttribute->getStoreLabel($filter);
            if ($seoLabel
                && strpos($string, $seoLabel) === 0
                && $seoLabel >= $matchedLabel
            ) {
                $matchedLabel = $seoLabel;
                $name[] = $filter->getName();
            }
        }

        return $name;
    }

    /**
     * Create SEO filter
     *
     * @param  string $filterName
     * @param  string $attributeName
     * @return Swissup\SeoUrls\Model\Filter\AbstractFilter | null
     */
    public function createSeoFilter($filterName, $attributeName = null)
    {
        if (isset($attributeName)) {
            $storeLabels = [];
            foreach ($this->attributeFiltersList as $f) {
                $isFilterLabelUnique = !in_array($f->getStoreLabel(), $storeLabels);
                if ($f->getName() == $attributeName) {
                    $seoFilter = $this->filterFactories
                        ->createFilter($filterName)
                        ->setLayerFilter($f);
                    // prevent seo labels overlapping
                    if (!$isFilterLabelUnique) {
                        $seoFilter->setLabel($f->getAttributeCode());
                    }

                    return $seoFilter;
                }

                if ($isFilterLabelUnique) {
                    array_push($storeLabels, $f->getStoreLabel());
                }
            }

            return null; // there are no attribute with $attributeName
        }

        return $this->filterFactories->createFilter($filterName);
    }

    /**
     * Force to load translates
     *
     * @return $this
     */
    protected function loadTranslates()
    {
        // no need to check if already loaded; area class implemented this check
        $area = $this->areaList->getArea($this->appState->getAreaCode());
        $area->load(\Magento\Framework\App\Area::PART_DESIGN);
        $area->load(\Magento\Framework\App\Area::PART_TRANSLATE);

        return $this;
    }

    /**
     * Warm up attributes seourls related attributes data.
     *
     * @return void
     */
    private function preloadAttributeData()
    {
        $attributeIds = [];
        foreach($this->attributeFiltersList as $filter) {
            $attributeIds[] = $filter->getId();
        }

        if ($attributeIds) {
            $this->attributeView->preloadData($attributeIds);
        }
    }
}
