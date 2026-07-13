<?php
/**
 * Generate search URL
 */
namespace Swissup\SeoUrls\Model\Url;

class Search extends \Magento\Framework\Url
{
    /**
     * Build URLs for search form
     *
     * @param   string|null $routePath
     * @param   array|null $routeParams
     * @return  string
     */
    public function getUrl($routePath = null, $routeParams = null)
    {
        $seoHelper = $this->getData('seoHelper'); // declared in di.xml
        if (isset($seoHelper) && $seoHelper->isSeoUrlsEnabled()) {
            if ($routePath == 'catalogsearch/result') {
                $target = $seoHelper->getSearchControllerName();
                $routePath = '';
                if (is_array($routeParams)) {
                    $routeParams['_direct'] = $target;
                } else {
                    $routeParams = ['_direct' => $target];
                }
            }
        }

        return parent::getUrl($routePath, $routeParams);
    }
}
