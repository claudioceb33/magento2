<?php
namespace Swissup\SeoHtmlSitemap\Block;

use \Magento\Framework\View\Element\Template\Context;
use \Magento\Framework\View\Element\Template;
use \Swissup\SeoHtmlSitemap\Helper\Config;
use \Swissup\SeoHtmlSitemap\Model\ResourceModel\Link\CollectionFactory;
use \Swissup\SeoHtmlSitemap\Model\Link as LinkModel;

class Custom extends Template implements \Magento\Framework\DataObject\IdentityInterface
{
    private $collectionFactory;
    private $config;
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        Config $config
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->config = $config;
        parent::__construct($context);
    }

    public function getCollection()
    {
        if (!$this->config->showCustomLinks()) {
            return false;
        }

        $collection = $this->collectionFactory->create();
        $collection->addStoreFilter($this->_storeManager->getStore()->getId());
        $collection->addFieldToFilter('status', 1);
        $collection->setOrder('name', 'ASC');

        return $collection;
    }

    public function getItemUrl($customLink)
    {
        return $this->_urlBuilder->getUrl(null, ['_direct' => $customLink->getUrl()]);
    }

    public function getItemName($customLink)
    {
        return $customLink->getName();
    }

    public function getIdentities()
    {
        return [LinkModel::CACHE_TAG . '_' . 'custom'];
    }
}
