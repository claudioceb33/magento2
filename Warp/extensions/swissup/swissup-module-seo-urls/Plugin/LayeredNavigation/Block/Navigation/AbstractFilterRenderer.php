<?php

namespace Swissup\SeoUrls\Plugin\LayeredNavigation\Block\Navigation;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\View\Element\Template;
use Swissup\SeoUrls\Helper\Data as Helper;
use Swissup\SeoUrls\Model\Attribute as SeoAttribute;

abstract class AbstractFilterRenderer
{
    protected Helper $helper;
    protected SeoAttribute $seoAttribute;

    public function __construct(
        Helper $helper,
        SeoAttribute $seoAttribute
    ) {
        $this->helper = $helper;
        $this->seoAttribute = $seoAttribute;
    }

    public function processNofollow(
        FilterInterface $filter,
        Template $block,
        $html
    ): string {
        $isForceNofollow = $this->isForceNofollow($filter);
        $isRemoveNofollow = $this->isRemoveNofollow($filter);

        if (!$isForceNofollow && !$isRemoveNofollow) {
            return $html;
        }

        $links = $this->getLinksFromHtml($html);
        foreach ($filter->getItems() as $filterItem) {
            $url = $filterItem->getActionUrl(); // Swissup ALN uses this method
            if (!$url) {
                $url = $filterItem->getUrl(); // Magento LN uses this method
            }

            $escapedUrl = $block->escapeUrl($url);
            $filteredLinks = array_filter($links, function ($link) use ($escapedUrl) {
                return strpos($link, $escapedUrl) !== false;
            });

            foreach ($filteredLinks as $link) {
                if ($isForceNofollow) {
                    if (strpos($link, 'rel="nofollow"') !== false) {
                        // link already has nofollow; skip it
                        continue;
                    }

                    $newLink = str_replace(
                        "href=\"{$escapedUrl}\"",
                        "href=\"{$escapedUrl}\" rel=\"nofollow\"",
                        $link
                    );
                    $html = str_replace($link, $newLink, $html);
                }

                if ($isRemoveNofollow) {
                    $newLink = str_replace('rel="nofollow"', '', $link);
                    $html = str_replace($link, $newLink, $html);
                }
            }
        }

        return $html;
    }

    public function isForceNofollow(FilterInterface $filter): bool
    {
        if ($this->isCategoryFilter($filter)) {
            return $this->helper->isCategoryFilterNofollowForce();
        }

        try {
            $attribute = $filter->getAttributeModel();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $attribute = null;
        }

        return $attribute ?
            $this->seoAttribute->isNofollowForce($attribute) :
            false;
    }

    public function isRemoveNofollow(FilterInterface $filter): bool
    {
        if ($this->isCategoryFilter($filter)) {
            return $this->helper->isCategoryFilterNofollowRemove();
        }

        try {
            $attribute = $filter->getAttributeModel();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $attribute = null;
        }

        return $attribute ?
            $this->seoAttribute->isNofollowRemove($attribute) :
            false;
    }

    public function getLinksFromHtml(string $html): array
    {
        // find all links in html
        preg_match_all('/<a[^>]*>/', $html, $matches);

        return $matches[0] ?? []; // array of full pattern matches
    }

    /**
     * @param  FilterInterface $filter
     * @return boolean
     */
    private function isCategoryFilter(FilterInterface $filter)
    {
        return (string)$filter->getName() === (string)__('Category');
    }
}
