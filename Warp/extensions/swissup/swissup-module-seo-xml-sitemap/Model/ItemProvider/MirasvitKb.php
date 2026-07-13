<?php
/**
 * Tested with mirasvit/module-kb:1.1.6
 */

namespace Swissup\SeoXmlSitemap\Model\ItemProvider;

use Magento\Framework\ObjectManagerInterface;
use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use Swissup\SeoXmlSitemap\Helper\Data as Helper;

class MirasvitKb implements ItemProviderInterface
{
    /**
     * @var SitemapItemInterfaceFactory
     */
    private $itemFactory;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @param SitemapItemInterfaceFactory $itemFactory
     * @param ObjectManagerInterface      $objectManager
     * @param Helper                      $helper
     */
    public function __construct(
        SitemapItemInterfaceFactory $itemFactory,
        ObjectManagerInterface $objectManager,
        Helper $helper
    ) {
        $this->itemFactory = $itemFactory;
        $this->objectManager = $objectManager;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems($storeId)
    {
        $items = [];
        if (!$this->helper->isModuleOutputEnabled('Mirasvit_Kb')) {
            return $items;
        }

        // Logic below is inspired with \Mirasvit\Kb\Model\Sitemap::_initSitemapItems
        $this->registerBasePath($storeId);
        $mirasvitHelper = $this->objectManager->get(\Mirasvit\Kb\Api\Data\SitemapInterface::class);
        $sitemapItems = [
            'blogItem' => $mirasvitHelper->getBlogItem($storeId),
            'categoryItems' => $mirasvitHelper->getCategoryItems($storeId),
            'postItems' => $mirasvitHelper->getPostItems($storeId)
        ];

        foreach ($sitemapItems as $data) {
            foreach ($data->getCollection() as $item) {
                $items[] = $this->itemFactory->create(
                    [
                        'url' => $item->getUrl(),
                        'updatedAt' => $item->getUpdatedAt(),
                        'images' => $item->getImages(),
                        'priority' => $data->getPriority(),
                        'changeFrequency' => $data->getChangeFrequency(),
                    ]
                );
            }
        };

        return $items;
    }

    private function registerBasePath($storeId): void
    {
        $config = $this->objectManager->get(\Mirasvit\Kb\Model\Config::class);
        $urlRewrite = $this->objectManager->get(\Mirasvit\Core\Api\UrlRewriteHelperInterface::class);
        $urlRewrite->registerBasePath('KBASE', $config->getBaseUrl($storeId));
    }
}
