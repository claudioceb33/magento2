<?php

namespace Swissup\SeoXmlSitemap\Model;

use Magento\Framework\DataObject;

class Sitemap extends \Magento\Sitemap\Model\Sitemap
{
    /**
     * @var \Swissup\Hreflang\Helper\Sitemap
     */
    protected $hreflangData;

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->hreflangData = $this->getData('hreflangData');
        parent::_construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSitemapItems()
    {
        parent::_initSitemapItems();
        $this->orderSitemapItemsByPriority();

        /* Swissup Hreflang intergartion */
        if ($this->hreflangData) {
            $this->hreflangData->injectXhtmlLinkSpecification($this->_tags);
        }
    }

    /**
     * @param  mixed $itemA
     * @param  mixed $itemB
     * @return boolean
     */
    protected function compareSitemapItems($itemA, $itemB)
    {
        if (!method_exists($itemA, 'getPriority')
            || !method_exists($itemB, 'getPriority')
        ) {
            return -1;
        }

        return (
            // Sort by priority
            $itemB->getPriority() > $itemA->getPriority() ||
            // then sort by latest modification date
            ($itemB->getPriority() == $itemA->getPriority() && $itemB->getUpdatedAt() > $itemA->getUpdatedAt()) ||
            // then sort by url
            ($itemB->getPriority() == $itemA->getPriority() && $itemB->getUrl() < $itemA->getUrl())
        ) ? 1 : -1;
    }

    /**
     * Order Sitemap items by priority
     *
     * @return $this
     */
    protected function orderSitemapItemsByPriority()
    {
        $callback = [$this, 'compareSitemapItems'];
        usort($this->_sitemapItems, $callback);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getSitemapRow($url, $lastmod = null, $changefreq = null, $priority = null, $images = null)
    {
        $xml = parent::_getSitemapRow($url, $lastmod, $changefreq, $priority, $images);
        /* Swissup Hreflang intergartion */
        if ($this->hreflangData) {
            $xml = $this->hreflangData->insertHreflag($xml, $url, $this->getStoreId());
        }

        return $xml;
    }

    /**
     * Add a sitemap item to the array of sitemap items
     *
     * @param DataObject $sitemapItem
     * @return $this
     * @since 100.2.0
     */
    public function addSitemapItem(DataObject $sitemapItem)
    {
        if (method_exists(\Magento\Sitemap\Model\Sitemap::class, 'addSitemapItem')) {
            return parent::addSitemapItem($sitemapItem);
        }

        $this->_sitemapItems[] = $sitemapItem;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * Compatibility with M2.1.x
     */
    protected function _getMediaUrl($url)
    {
        if (strpos($url, 'http') === 0) {
            return $url;
        }

        return parent::_getMediaUrl($url);
    }
}
