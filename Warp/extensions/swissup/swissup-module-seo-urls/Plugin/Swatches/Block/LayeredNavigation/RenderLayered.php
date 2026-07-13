<?php

namespace Swissup\SeoUrls\Plugin\Swatches\Block\LayeredNavigation;

use Swissup\SeoUrls\Plugin\LayeredNavigation\Block\Navigation\AbstractFilterRenderer;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\View\Element\Template;

class RenderLayered extends AbstractFilterRenderer
{
    /**
     * @var FilterInterface
     */
    private $filter;

    /**
     * Plugin to catch filter
     * (added for Magento 2.1.x support
     *
     * @param  Template        $subject
     * @param  FilterInterface $filter
     */
    public function beforeSetSwatchFilter(
        Template $subject,
        FilterInterface $filter
    ) {
        $this->filter = $filter;
    }

    /**
     * Plugin to add rel="nofollow" into rendered links
     *
     * @param  Template $subject
     * @param  string   $result
     * @return string
     */
    public function afterToHtml(Template $subject, $result)
    {
        if ($this->helper->isSeoUrlsEnabled()) {
            $result = $this->processNofollow($this->filter, $subject, $result);
        }

        return $result;
    }
}
