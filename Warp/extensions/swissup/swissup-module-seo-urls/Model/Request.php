<?php

namespace Swissup\SeoUrls\Model;

use Swissup\SeoUrls\Helper\Filter as HelperFilter;
use Swissup\SeoUrls\Model\Category as SeoCategory;
use Swissup\SeoUrls\Model\Filter\AbstractFilter as SeoFilter;
use Swissup\SeoUrls\Model\ResourceModel\Category\View as ResourceCategoryView;

class Request
{
    private HelperFilter $filterHelper;
    private SeoCategory $seoCategory;
    private ResourceCategoryView $categoryView;

    public function __construct(
        HelperFilter $filterHelper,
        SeoCategory $seoCategory,
        ResourceCategoryView $categoryView
    ) {
        $this->filterHelper = $filterHelper;
        $this->seoCategory = $seoCategory;
        $this->categoryView = $categoryView;
    }

    /**
     * Get get paraneters in strings
     */
    public function getParamsFromString(
        string $queryString,
        int $categoryId
    ): array {
        $params = [];
        $leftovers = [];
        $queryString = rawurldecode($queryString); // Decode URL-encoded strings
        $strings = array_filter(explode('/', $queryString));
        foreach ($strings as $string) {
            $filterNames = $this->filterHelper->findFilterInString($string);
            $leftover = '';
            // $filterNames is an array. Because multiple filters can have the same seo label.
            // We still try to resolve them.
            foreach ($filterNames as $filterName) {
                $value = null;
                if ($filterName == $this->filterHelper->getPredefinedFilterRequestVar('category_filter')) {
                    $valueLabel = str_replace(
                        $this->filterHelper->getPredefinedFilterLabel('category_filter') . '-',
                        '',
                        $string
                    );
                    $value = $this->findoutCategoryId($valueLabel, $categoryId);
                } elseif ($filterName
                    && ($seoFilter = $this->filterHelper->getByName($filterName))
                    && $seoFilter
                ) {
                    $value = $this->resolveFilterValueFromString(
                        $seoFilter,
                        $string,
                        $leftover
                    );
                    // Update $string. Filter name + unresolved characters.
                    $string = $seoFilter->getLabel() . '-' . $leftover;
                }

                if ($value !== null) {
                    $params[$filterName] = $value;
                }
            }

            $leftovers[] = $leftover;
        }

        return array_filter(
            $params + ['seo_leftovers' => array_filter($leftovers)],
            function ($v) {
                return !!$v || $v === 0 || $v === '0';
            }
        );
    }

    private function resolveFilterValueFromString(
        SeoFilter $seoFilter,
        string $string,
        ?string &$leftover = ''
    ): ?string {
        $filterLabel = $seoFilter->getLabel() . '-';
        $position = strpos($string, $filterLabel);
        if ($position !== false) {
            $label = substr_replace(
                $string,
                '',
                $position,
                strlen($filterLabel)
            );
        } else {
            $label = $string;
        }

        $options = $seoFilter->getOptions();

        if (!$options) {
            // there are no options for filter.
            // perhaps if is some number range filter
            return $label;
        }

        $value = array_search($label, $options);
        if ($value !== false) {
            $leftover = '';

            return $value;
        }

        // value not found
        // perhaps there is some layered navigation to select multiple values
        $value = null;
        $iterations = 0; // prevent infinit loop
        do {
            $bestMatchLabel = '';
            $bestMatchValue = '';
            foreach ($options as $k => $l) {
                if (strpos($label, $l) === 0 && $l > $bestMatchLabel) {
                    $bestMatchLabel = $l;
                    $bestMatchValue = $k;
                }
            }
            if ($bestMatchLabel) {
                if ($value === null) {
                    $value = '';
                }
                $value .= $bestMatchValue . ',';
                unset($options[$bestMatchValue]);
                $label = substr($label, strlen($bestMatchLabel));
                $label = ltrim($label, '-');
            }
            $iterations++;
        } while (!empty($bestMatchLabel) && $iterations < 100);


        // Some character left unresolved from initial string.
        $leftover = $label ?: '';

        return $value === null ? $value : rtrim($value, ',');
    }

    /**
     * Try to find out category id by its seo-label
     *
     * @param  string $categorySeoLabel
     * @param  int $categoryId ID of current category
     * @return
     */
    public function findoutCategoryId($categorySeoLabel, $categoryId)
    {
        if (!$categorySeoLabel) {
            return $categoryId;
        }

        $category = $categoryId === null ?
            $this->filterHelper->getRootCategory() :
            $this->filterHelper->getCategoryById($categoryId);

        return $this->getCategoryChildIdByLabel($category, $categorySeoLabel, '');

    }

    /**
     * Get ID of category by its SEO label
     *
     * @param  \Magento\Catalog\Api\Data\CategoryInterface $category
     * @param  string $valueLabel
     * @param  string $prefix
     * @return int|null
     */
    private function getCategoryChildIdByLabel($category, $valueLabel, $prefix)
    {
        $o = [];
        $children = $category->getChildrenCategories();
        $this->categoryView->preloadData(
            array_merge(
                $this->filterHelper->getIdsFromCategories($children),
                [$category->getId(), $category->getParentId()]
            )
        );
        foreach ($children as $c) {
            $o[$prefix . $this->seoCategory->getStoreLabel($c)] = $c;
        }

        if (isset($o[$valueLabel])) {
            return $o[$valueLabel]->getId();
        }

        krsort($o);
        foreach ($o as $seoLabel => $c) {
            if (strpos($valueLabel, $seoLabel) === 0) {
                return $this->getCategoryChildIdByLabel($c, $valueLabel, $seoLabel . '-');
            }
        }

        return null;
    }

    public function mergeAndAppendValues(array $array1, array $array2)
    {
        foreach ($array2 as $key => $value) {
            if (isset($array1[$key])) {
                $_value = explode(',', (string)$array1[$key]);
                $value  = explode(',', (string)$value);
                $_value = array_merge($_value, $value);
                $_value = array_filter($_value);
                $_value = array_unique($_value);
                sort($_value);
                $value = implode(',', $_value);
            }
            $array1[$key] = $value;
        }

        if ($this->filterHelper->isModuleOutputEnabled('Smile_ElasticsuiteCatalog')) {
            // Compatibility with Smile_ElasticsuiteCatalog.
            // We provide multiple values for filter as comma separated string.
            // But Smile expects multiple values for filter as an array.
            $array1 = array_map(function ($value) {
                if (is_string($value)) {
                    $value = explode(',', $value);
                    $value = count($value) == 1 ? reset($value) : $value;
                }

                return $value;
            }, $array1);
        }

        return $array1;
    }
}
