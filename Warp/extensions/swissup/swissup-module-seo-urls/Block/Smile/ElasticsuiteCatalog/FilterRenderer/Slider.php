<?php

namespace Swissup\SeoUrls\Block\Smile\ElasticsuiteCatalog\FilterRenderer;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\UrlInterface;
use Magento\LayeredNavigation\Block\Navigation\FilterRendererInterface;

class Slider extends AbstractBlock implements FilterRendererInterface
{
    /**
     * @var \Magento\Catalog\Model\Layer\Filter\FilterInterface
     */
    private $filter;
    /**
     * {@inheritdoc}
     */
    public function render(FilterInterface $filter)
    {
        if (!$this->canRender()) {
            return '';
        }

        $seoHelper = $this->getData('seoHelper');
        $seoFilter = $seoHelper->getByName($filter->getRequestVar());
        if (!$seoFilter) {
            return '';
        }

        $html = '';
        $this->filter = $filter;

        $renderer = $this->getRenderer();
        $html = $renderer ? $renderer->render($filter) : '';
        if ($html) {
            $regexp      = "/(\\/{$seoFilter->getLabel()})-(-?[0-9]+)/";
            $replacement = '${1}-<%- from %>-<%- to %>';
            $html = preg_replace($regexp, $replacement, $html);
        }

        return $html;
    }

    public function canRender(): bool
    {
        $seoHelper = $this->getData('seoHelper');
        if ($seoHelper // seo helper assigned
            && $seoHelper->isSeoUrlsEnabled() // Swissup_SeoUrls enabled
            // Smile_ElasticsuiteCalatog enabled
            && $seoHelper->isModuleOutputEnabled('Smile_ElasticsuiteCatalog')
            // there is an alias for URL; not exists when url is catalog/category/view/id
            && $this->getRequest()->getAlias(UrlInterface::REWRITE_REQUEST_PATH_ALIAS)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get original Smile renderer from layout
     *
     * @return FilterRendererInterface
     */
    protected function getRenderer()
    {
        $originalRenderer = $this->getData('originalRenderer');

        return $this->_layout->getBlock($originalRenderer);
    }
}
