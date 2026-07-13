<?php

namespace Swissup\Hreflang\Helper;

use Magento\Cms\Api\Data\PageInterfaceFactory as PageFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Sitemap\Model\Sitemap as XmlSitemap;
use Magento\Store\Api\Data\StoreInterface;
use Swissup\Hreflang\Model\ResourceModel\Page as PageResource;
use Swissup\Hreflang\Model\ResourceModel\Product as ProductResource;
use Swissup\Hreflang\Model\ResourceModel\Category as CategoryResource;

class Sitemap
{
    private Escaper $escaper;

    protected PageFactory $pageFactory;
    protected PageResource $pageResource;
    protected ProductResource $productResource;
    protected CategoryResource $categoryResource;
    protected array $items;

    public function __construct(
        Escaper $escaper,
        PageFactory $pageFactory,
        PageResource $pageResource,
        ProductResource $productResource,
        CategoryResource $categoryResource
    ) {
        $this->escaper = $escaper;
        $this->pageFactory = $pageFactory;
        $this->pageResource = $pageResource;
        $this->productResource = $productResource;
        $this->categoryResource = $categoryResource;
        $this->items = [];
    }

    public function addItem(
        int $storeId,
        string $url,
        DataObject $item
    ): self {
        $this->items[(int)$storeId . '::' . $url] = $item;

        return $this;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getItem(int $storeId, string $url): ?DataObject
    {
        $key = $storeId . '::' . $url;

        return $this->items[$key] ?? null;
    }

    public function cleanItems(): self
    {
        $this->items = [];
        return $this;
    }

    /**
     * Specify the xhtml namespace
     */
    public function injectXhtmlLinkSpecification(array &$sitemapTags): void
    {
        // Hreflang requires xhtml namespace specification as it is below.
        // https://support.google.com/webmasters/answer/189077?hl=en
        $xhtmlLinkDef = ' xmlns:xhtml="http://www.w3.org/1999/xhtml"';
        // But it breaks XML preview in browsers.
        // For testing pursopes uncoment like below.
        // https://productforums.google.com/forum/#!topic/webmasters/0hxIjDJRZNc
        // $xhtmlLinkDef = ' xmlns:xhtml="http://www.w3.org/TR/xhtml11/xhtml11_schema.html"';
        $openTagKey = $sitemapTags[XmlSitemap::TYPE_URL][XmlSitemap::OPEN_TAG_KEY];
        $sitemapTags[XmlSitemap::TYPE_URL][XmlSitemap::OPEN_TAG_KEY] =
            str_replace(
                ' xmlns:image=',
                $xhtmlLinkDef .' xmlns:image=',
                $openTagKey
            );
    }

    /**
     * Insert hreflang links into xml row for
     *
     * @param  string $xmlRow
     * @param  string $url
     * @param  int    $storeId
     * @return string
     */
    public function insertHreflag(
        string $xmlRow,
        string $url,
        int $storeId
    ): string {
        $hreflang = $this->getItem($storeId, $url);
        if ($hreflang && $hreflang->hasData('collection')) {
            $extraXml = '';
            foreach ($hreflang->getCollection() as $lang => $href) {
                $href = $this->escaper->escapeUrl($href);
                $extraXml .= "<xhtml:link rel=\"alternate\" hreflang=\"{$lang}\" href=\"{$href}\" />";
            }

            $xmlRow = str_replace('</url>', $extraXml . '</url>', $xmlRow);
        }

        return $xmlRow;
    }

    public function getHreflangIdentifier(
        DataObject $itemPage,
        StoreInterface $store
    ): string {
        $page = $this->pageFactory->create()->setData([
            'id' => $itemPage->getId(),
            'page_id' => $itemPage->getId()
        ]);

        $identifier = $this->pageResource->getHreflangIdentifier($page, $store);
        if (!$identifier) {
            // HreflangIndenifier for page with ID $pageId at $store not found.
            // Try to use page url.
            $identifier = $page->checkIdentifier($itemPage->getUrl(), $store->getId()) ?
                $itemPage->getUrl() :
                '';
        }

        return $identifier;
    }

    public function preloadProductStatusData(array $items)
    {
        $this->productResource->preloadStatusData($items);
    }

    public function isProductEnabled(
        DataObject $product,
        StoreInterface $store
    ): bool {
        return $this->productResource->isEnabled($product, $store);
    }

    public function preloadCategoryStatusData(array $items): void
    {
        $this->categoryResource->preloadStatusData($items);
    }

    public function isCategoryEnabled(
        DataObject $category,
        StoreInterface $store
    ): bool {
        return $this->categoryResource->isEnabled($category, $store);
    }
}
