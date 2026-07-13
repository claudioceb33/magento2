<?php

namespace Swissup\SeoPager\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template as ListBlock;
use Magento\Framework\View\LayoutInterface;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Theme\Block\Html\Pager;

class ToolbarResolver
{
    protected LayoutInterface $layout;
    protected array $layoutBlockName;
    protected $toolbarBlock;

    private int $lastPageNumber = 0;

    public function __construct(
        LayoutInterface $layout,
        array $layoutBlockName = []
    ) {
        $this->layout = $layout;
        $this->layoutBlockName = $layoutBlockName;
    }

    public function getToolbarBlock(): Toolbar
    {
        if (!$this->toolbarBlock) {
            $this->toolbarBlock = $this->_findToolbarBlock();
        }

        return $this->toolbarBlock;
    }

    private function _findToolbarBlock(): Toolbar
    {
        $possibleNames = $this->layoutBlockName ?? [];
        foreach ($possibleNames as $name) {
            $listing = $this->layout->getBlock($name);
            if (!$listing) {
                continue;
            }

            $toolbar = $listing->getToolbarBlock();
            if ($toolbar
                && $toolbar instanceof Toolbar
            ) {
                if (!$toolbar->getCollection()) {
                    $collection = $listing->getLoadedProductCollection();
                    $this->configureToolbar($listing, $toolbar, $collection);
                }

                return $toolbar;
            }

        }

        throw new LocalizedException(__('Unable to find toolbar block in current layout.'));
    }

    public function getPagerBlock(): Pager
    {
        $toolbar = $this->getToolbarBlock();
        foreach ($toolbar->getChildNames() as $name) {
            $pager = $this->layout->getBlock($name);
            if ($pager instanceof Pager) {
                return $pager;
            }
        }

        throw new LocalizedException(__('Unable to find pager block in current layout.'));
    }

    /**
     * Copied from Magento\Catalog\Block\Product\ListProduct::configureToolbar
     *
     * Initialize toolbar for listing
     */
    private function configureToolbar(
        ListBlock $listing,
        Toolbar $toolbar,
        $collection
    ): void {
        // use sortable parameters
        $orders = $listing->getAvailableOrders();
        if ($orders) {
            $toolbar->setAvailableOrders($orders);
        }
        $sort = $listing->getSortBy();
        if ($sort) {
            $toolbar->setDefaultOrder($sort);
        }
        $dir = $listing->getDefaultDirection();
        if ($dir) {
            $toolbar->setDefaultDirection($dir);
        }
        $modes = $listing->getModes();
        if ($modes) {
            $toolbar->setModes($modes);
        }
        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);
        $listing->setChild('toolbar', $toolbar);
    }

    public function getLastPageNumber(): int
    {
        if (!$this->lastPageNumber) {
            $collection = clone $this->getToolbarBlock()->getCollection();
            $this->lastPageNumber = $collection->getLastPageNumber();
        }

        return (int) $this->lastPageNumber;
    }

    public function getCurrPageNumber(): int
    {
        return (int) $this->getToolbarBlock()->getCurrentPage();
    }
}
