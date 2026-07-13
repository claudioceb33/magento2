<?php

namespace Swissup\SeoXmlSitemap\Model\ItemProvider;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use Swissup\SeoXmlSitemap\Helper\Data as Helper;

class OtherLinks implements ItemProviderInterface
{
    /**
     * @var SitemapItemInterfaceFactory
     */
    protected $itemFactory;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var string
     */
    protected $collectionClass;

    /**
     * @param SitemapItemInterfaceFactory $itemFactory
     * @param ObjectManagerInterface      $objectManager
     * @param Helper                      $helper
     * @param string                      $collectionClass
     */
    public function __construct(
        SitemapItemInterfaceFactory $itemFactory,
        ObjectManagerInterface $objectManager,
        Helper $helper,
        string $collectionClass = null
    ) {
        $this->itemFactory = $itemFactory;
        $this->objectManager = $objectManager;
        $this->helper = $helper;
        $this->collectionClass = $collectionClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems($storeId)
    {
        $collection = $this->getCollection();
        if (!$collection) {
            return [];
        }

        $items = array_map(function ($item) use ($storeId) {
            return $this->itemFactory->create([
                'url' => $item->getUrl(),
                'updatedAt' => $item->getUpdateTime(),
                'images' => $item->getImages(),
                'priority' => $this->helper->getOtherPriority($storeId),
                'changeFrequency' => $this->helper->getOtherChangefreq($storeId),
            ]);
        }, $collection->addStoreFilter($storeId)->getItems());

        return $items;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    private function getCollection()
    {
        if ($this->collectionClass
            && class_exists($this->collectionClass)
        ) {
             return $this->objectManager->create($this->collectionClass);
        }

        return null;
    }
}
