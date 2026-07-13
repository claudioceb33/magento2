<?php

namespace Swissup\SeoUrls\Model\CurrentUrl;

use Swissup\SeoCore\Model\CurrentUrl\ProviderInterface;

abstract class AbstractProvider implements ProviderInterface
{
    /**
     * @var \Swissup\SeoUrls\Model\Url\Filter
     */
    protected $seoUrlBuilder;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $emulation;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param \Swissup\SeoUrls\Model\Url\Filter       $seoUrlBuilder
     * @param \Magento\Store\Model\App\Emulation      $emulation
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Swissup\SeoUrls\Model\Url\Filter $seoUrlBuilder,
        \Magento\Store\Model\App\Emulation $emulation,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->seoUrlBuilder = $seoUrlBuilder;
        $this->emulation = $emulation;
        $this->request = $request;
    }

    /**
     * Get pathInfo from request
     *
     * @return string
     */
    public function getRequestPathInfo()
    {
        return (string)$this->request->getAlias(
            \Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS
        );
    }

    /**
     * Get applied url filters
     *
     * @return array
     */
    public function getAppliedFilters()
    {
        $urlFilters = [];
        $seoHelper = $this->seoUrlBuilder->getData('seoHelper');
        // we can not use $this->seoUrlBuilder->getFiltersStateAsQuery()
        // because current state of layer is not initialized
        // so i take all parameters from request
        $query = $this->request->getParams();
        foreach ($query as $key => $value) {
            if (isset($value)) {
                $seoFilter = $seoHelper->getByName($key);
                if (isset($seoFilter)) {
                    $pairFilterValue = $this->seoUrlBuilder
                        ->getPairFilterValue($seoFilter, $value);
                    if ($pairFilterValue) {
                        $sortOrder = $seoFilter->getSortOrder();
                        $urlFilters[$sortOrder] = $pairFilterValue;
                    }
                }
            }
        }

        ksort($urlFilters);
        if ($seoHelper->isSeparateFilters() && !empty($urlFilters)) {
            // set Filter Separator as first elemet of array
            array_unshift($urlFilters, $seoHelper->getFiltersSeparator());
        }

        return $urlFilters;
    }
}
