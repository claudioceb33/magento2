<?php

namespace Swissup\SeoPager\Plugin\View\Page\Config;

use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Page\Config\Renderer as Subject;
use Swissup\SeoPager\Helper\Data;
use Swissup\SeoPager\Model\Config\Source\Strategy;
use Swissup\SeoPager\Model\ToolbarResolver;
use Swissup\SeoPager\Model\Filter\Title as TitleFilter;

class Renderer
{
    private Data $helper;
    private LayerResolver $layerResolver;
    private PageConfig $pageConfig;
    private TitleFilter $titleFilter;
    private ToolbarResolver $toolbarResolver;

    public function __construct(
        Data $helper,
        LayerResolver $layerResolver,
        PageConfig $pageConfig,
        TitleFilter $titleFilter,
        ToolbarResolver $toolbarResolver
    ) {
        $this->helper = $helper;
        $this->layerResolver = $layerResolver;
        $this->pageConfig = $pageConfig;
        $this->titleFilter = $titleFilter;
        $this->toolbarResolver = $toolbarResolver;
    }

    public function afterRenderTitle(
        Subject $subject,
        string $result
    ) {
        $titleHtml = $result;
        if ($this->helper->isEnabled()) {
            if ($this->titleFilter->currentPageDirective() > 1
                && strpos($titleHtml, '<title>') !== false
                && strpos($titleHtml, '</title>') !== false
            ) {
                list($beforeTitle, $afterTitle) = explode('<title>', $titleHtml, 2);
                list($title ,$afterTitle) = explode('</title>', $afterTitle, 2);
                $titleHtml = $beforeTitle .
                    '<title>' .
                    $this->generateTitle($title) .
                    '</title>' .
                    $afterTitle;
            }
        }

        return $titleHtml;
    }

    private function generateTitle(string $originalTitle): string
    {
        $object = new \Magento\Framework\DataObject(['title' => $originalTitle]);
        $this->titleFilter->setScope($object);
        $template = $this->helper->getTitleTemplate();

        return $this->titleFilter->filter($template);
    }

    public function beforeRenderHeadContent(
        Subject $subject
    ) {
        $helper = $this->helper;
        if (!$helper->isEnabled()) {
            return null;
        }

        try {
            $this->toolbarResolver->getToolbarBlock();
        } catch (\Exception $e) {
            return null;
        }

        switch ((int)$helper->getPresentationStrategy()) {

            // Canonical URL - "View All" page
            case Strategy::REL_CANONICAL:
                $this->removeCanonical();
                $this->addCanonical($helper->getViewAllPageUrl(false));

                break;

            // Canonical URL - current page of paginated content
            // (when additional filters applied, order changed etc. - NOINDEX)
            case Strategy::REL_CANONICAL_PER_PAGE:
                $currPage = $this->toolbarResolver->getCurrPageNumber();
                $lastPage = $this->toolbarResolver->getLastPageNumber();
                if ($currPage > $lastPage) {
                    $url = $helper->getPageUrl($lastPage, false);
                } else {
                    $url = $helper->getPageUrl($currPage, false);
                }

                $this->removeCanonical();
                $this->addCanonical($url);
                $this->updateRobots();

                // prefetch next page if possible
                if ($currPage < $lastPage) {
                    $this->pageConfig->addRemotePageAsset(
                        $helper->getPageUrl($currPage + 1, true),
                        'prefetch',
                        ['attributes' => ['rel' => 'prefetch', 'as' => 'document']]
                    );
                }

                break;

            // Canonical URL not affected. Add rel=prev and rel=next.
            // (Deprecated)
            case Strategy::REL_NEXT_REL_PREV:
                $currPage = $this->toolbarResolver->getCurrPageNumber();
                $lastPage = $this->toolbarResolver->getLastPageNumber();
                if ($currPage > 1) {
                    $this->pageConfig->addRemotePageAsset(
                        $helper->getPageUrl($currPage - 1, true),
                        'pager',
                        ['attributes' => ['rel' => 'prev']]
                    );
                }

                if ($currPage < $lastPage) {
                    $this->pageConfig->addRemotePageAsset(
                        $helper->getPageUrl($currPage + 1, true),
                        'pager',
                        ['attributes' => ['rel' => 'next']]
                    );
                }

                break;
        }

        return null;
    }

    private function removeCanonical(): void
    {
        $assetCollection = $this->pageConfig->getAssetCollection();
        $groupCanonical = $assetCollection->getGroupByContentType('canonical');
        $identifiers = array_keys($groupCanonical ? $groupCanonical->getAll() : []);
        array_walk($identifiers, [$assetCollection, 'remove']);
    }

    private function addCanonical(string $url): void
    {
        $this->pageConfig->addRemotePageAsset(
            $url,
            'canonical',
            ['attributes' => ['rel' => 'canonical']]
        );
    }

    private function updateRobots(): void
    {
        $toolbar = $this->toolbarResolver->getToolbarBlock();
        $options = $this->getToolbarOptions();
        if ((int)$toolbar->getLimit() !== (int)$options->getData('limitDefault')
            || $toolbar->getCurrentMode() !== $options->getData('modeDefault')
            || $toolbar->getCurrentDirection() !== $options->getData('directionDefault')
            || $toolbar->getCurrentOrder() !== $options->getData('orderDefault')
        ) {
            $this->pageConfig->setRobots('NOINDEX,NOFOLLOW');
            return;
        }

        $layer = $this->layerResolver->get();
        $filters = $layer->getState()->getFilters();
        if ($filters) {
            // There are applied filter - add NOINDEX
            $this->pageConfig->setRobots('NOINDEX,NOFOLLOW');
            return;
        }
    }

    private function getToolbarOptions(): \Magento\Framework\DataObject
    {
        $toolbar = $this->toolbarResolver->getToolbarBlock();
        $options = json_decode($toolbar->getWidgetOptionsJson(), true);

        return new \Magento\Framework\DataObject($options['productListToolbarForm']);
    }
}
