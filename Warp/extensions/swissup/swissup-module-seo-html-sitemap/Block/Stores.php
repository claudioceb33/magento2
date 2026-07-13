<?php

namespace Swissup\SeoHtmlSitemap\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory;
use Swissup\SeoHtmlSitemap\Helper\Config;
use Swissup\SeoHtmlSitemap\Model\Link as LinkModel;

class Stores extends Template implements \Magento\Framework\DataObject\IdentityInterface
{
    private CollectionFactory $collectionFactory;
    private Config $config;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        Config $config
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
        $this->config = $config;
    }

    public function getCollection()
    {
        if (!$this->config->showStores()) {
            return false;
        }

        return $this->collectionFactory
            ->create()
            ->addFieldToFilter(
                'website_id',
                $this->_storeManager->getWebsite()->getId()
            );
    }

    public function getItemUrl($store)
    {
        return $store->getUrl();
    }

    public function getItemName($store)
    {
        return $store->getName();
    }

    public function getIdentities()
    {
        return [LinkModel::CACHE_TAG . '_' . 'stores'];
    }
}
