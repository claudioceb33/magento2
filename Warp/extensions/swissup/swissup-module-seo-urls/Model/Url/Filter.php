<?php
/**
 * Generate URLs for layered navigation filters, remove filter and clear all
 */

namespace Swissup\SeoUrls\Model\Url;

class Filter extends \Magento\Framework\Url
{
    /**
     * Build URLs for layered navigation on product listing and search page
     * @param   string|null $routePath
     * @param   array|null $routeParams
     * @return  string
     */
    public function getUrl($routePath = null, $routeParams = null)
    {
        $seoHelper = $this->getData('seoHelper');
        if (!isset($seoHelper) // seoHelper is undefined
            || !$seoHelper->isSeoUrlsEnabled() // module disabled
            || !$this->_getRequest()->getAlias(self::REWRITE_REQUEST_PATH_ALIAS)
               // there is no alias for URL; perhaps URL is catalog/category/view/id
        ) {
            return parent::getUrl($routePath, $routeParams);
        }

        $query = !empty($routeParams['_current']) // add current request values
            ? $this->getFiltersStateAsQuery()
            : [];
        if (isset($routeParams['_query'])) {
            $query = array_merge($query, $routeParams['_query']);
        }

        // prepare url part with filters
        $urlFilters = [];
        foreach ($query as $key => $value) {
            if (isset($value)) {
                $seoFilter = $seoHelper->getByName($key);
                if (isset($seoFilter)) {
                    $pairFilterValue = $this->getPairFilterValue($seoFilter, $value);
                    if ($pairFilterValue) {
                        $sortOrder = $seoFilter->getSortOrder();
                        $urlFilters[$sortOrder] = $pairFilterValue;
                        // unset processed filter
                        // unset($query[$key]);
                        $query[$key] = null;
                    }
                }
            }
        }

        if (empty($urlFilters)) {
            // no filters applied - use magento generated url
            return parent::getUrl($routePath, $routeParams);
        }

        ksort($urlFilters);
        if ($seoHelper->isSeparateFilters()) {
            array_unshift($urlFilters, $seoHelper->getFiltersSeparator());
        }

        // overwrite query for url
        $this->_queryParamsResolver->setQueryParams($query);
        $routeParams['_query'] = $query;
        // get url using parent method
        $url = parent::getUrl($routePath, $routeParams);
        // rebuild url
        return $this->getData('seoUrl')->rebuild($url, $urlFilters);
    }

    /**
     * Get pair filet-value for seo url
     *
     * @param  \Swissup\SeoUrls\Model\Filter\AbstractFilter $filter
     * @param  string $value
     * @return string
     */
    public function getPairFilterValue(
        \Swissup\SeoUrls\Model\Filter\AbstractFilter $filter,
        $value
    ) {
        $options = $filter->getOptions();
        $seoValue = '';
        if ($options) {
            $value = is_array($value) ? implode(',', $value) : $value;
            if (isset($options[$value])) {
                // default magento layered navigation
                $seoValue = (string)$options[$value];
            } else {
                // swissup ajax layered navigation
                // or other LN that allows select multiple values
                $valueArray = explode(',', (string)$value);
                $v = '';
                $seoVs = [];
                do {
                    $v.= array_shift($valueArray);
                    if ($v !== null && isset($options[$v])) {
                        $seoVs[$v] = $options[$v];
                        $v = '';
                    } else {
                        $v.= ',';
                    }
                } while (!empty($valueArray));
                ksort($seoVs);
                $seoValue = implode('-', $seoVs);
            }
        } else {
            $seoValue = is_array($value) ? implode('-', $value) : (string)$value;
        }

        return strlen($seoValue)
            ? ($filter->getLabel() . '-' . $seoValue)
            : '';
    }

    /**
     * Get filters state as query array
     *
     * @return array
     */
    public function getFiltersStateAsQuery()
    {
        $q = [];
        $catalogLayer = $this->getData('layerResolver')->get();
        foreach ($catalogLayer->getState()->getFilters() as $f) {
            $name = $f->getFilter()->getRequestVar();
            $value = $f->getValue();
            if (isset($q[$name]) && !is_array($value)) {
                $q[$name] .= ',' . (string)$value;
            } else {
                $q[$name] = $value;
            }
        }

        return $q;
    }
}
