<?php

namespace Swissup\SeoUrls\Plugin\LayeredNavigation\Block\Navigation;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\View\Element\Template;

class FilterRenderer extends AbstractFilterRenderer
{
    /**
     * @var FilterInterface
     */
    private $filter;

    /**
     * Plugin to catch filter
     * (added for Magento 2.1.x support)
     *
     * @param  Template        $subject
     * @param  FilterInterface $filter
     */
    public function beforeRender(Template $subject, FilterInterface $filter)
    {
        $this->filter = $filter;
    }

    /**
     * Plugin to add rel="nofollow" into rendered links
     * Plugin to disable ajax for filter (powered by Swissup ALN)
     *
     * @param  Template $subject
     * @param  string   $result
     * @return string
     */
    public function afterRender(Template $subject, $result)
    {
        if ($this->helper->isSeoUrlsEnabled()) {
            $result = $this->processNofollow($this->filter, $subject, $result);
        }

        return $result;
    }
}
