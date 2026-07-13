<?php

namespace Swissup\SeoUrls\Model\Layer\Filter;

use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\Framework\App\RequestInterface;

class ItemCategory extends \Magento\Catalog\Model\Layer\Filter\Item
{
    /**
     * @var UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * @var \Swissup\SeoUrls\Helper\Data
     */
    protected $helper;

    /**
     * @param UrlFinderInterface              $urlFinder
     * @param \Swissup\SeoUrls\Helper\Data    $helper
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Theme\Block\Html\Pager $htmlPagerBlock
     * @param array                           $data
     */
    public function __construct(
        UrlFinderInterface $urlFinder,
        \Swissup\SeoUrls\Helper\Data $helper,
        \Magento\Framework\UrlInterface $url,
        \Magento\Theme\Block\Html\Pager $htmlPagerBlock,
        array $data = []
    ) {
        $this->urlFinder = $urlFinder;
        $this->helper = $helper;
        parent::__construct($url, $htmlPagerBlock, $data);
    }

    /**
     * {inheritdoc}
     */
    public function getUrl()
    {
        if (!$this->forceDirectSubcategoryUrl()) {
            return parent::getUrl();
        }

        $rewrite = $this->urlFinder->findOneByData(
            [
                UrlRewrite::ENTITY_ID => $this->getValue(),
                UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
                UrlRewrite::STORE_ID => $this->helper->getCurrentStore()->getId(),
            ]
        );

        $query = [
            // exclude current page from urls
            $this->_htmlPagerBlock->getPageVarName() => null,
        ];

        return $this->_url->getUrl('*/*/*', [
            '_current' => true,
            '_direct' => $rewrite ? $rewrite->getRequestPath() : "catalog/category/view/id/{$this->getValue()}",
            '_query' => $query
        ]);
    }

    /**
     * [Swissup_Ajaxlayerednavigation]
     *
     * @return string
     */
    public function getActionUrl()
    {
        if (!$this->forceDirectSubcategoryUrl()) {
            return parent::getUrl();
        }

        return $this->getUrl();
    }

    /**
     * [Swissup_Ajaxlayerednavigation]
     *
     * @return string
     */
    public function getRequestVar()
    {
        return 'cat';
    }

        /**
     * [Swissup_Ajaxlayerednavigation]
     * @return string
     */
    public function getResetUrl()
    {
        $urlParams = [
            '_current' => true,
            '_use_rewrite' => true,
            '_query' => [
                $this->getRequestVar() => null,
            ],
            '_escape' => true,
        ];
         return $this->_url->getUrl('*/*/*',  $urlParams);
    }

    /**
     * @return boolean
     */
    public function forceDirectSubcategoryUrl()
    {
        return $this->helper->isSeoUrlsEnabled()
            && $this->helper->isForceSubcategoryUrl();
    }
}
