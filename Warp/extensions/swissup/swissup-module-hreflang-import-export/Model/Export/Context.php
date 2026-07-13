<?php

namespace Swissup\HreflangImportExport\Model\Export;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\ImportExport\Model\Export\Factory as CollectionFactory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\Store\Model\StoreManagerInterface;

class Context
{
    private CollectionByPagesIteratorFactory $resourceColFactory;
    private CollectionFactory $collectionFactory;
    private ScopeConfigInterface $scopeConfig;
    private StoreManagerInterface $storeManager;

    public function __construct(
        CollectionByPagesIteratorFactory $resourceColFactory,
        CollectionFactory $collectionFactory,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->resourceColFactory = $resourceColFactory;
        $this->collectionFactory = $collectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    public function getResourceColFactory(): CollectionByPagesIteratorFactory
    {
        return $this->resourceColFactory;
    }

    public function getCollectionFactory(): CollectionFactory
    {
        return $this->collectionFactory;
    }

    public function getScopeConfig(): ScopeConfigInterface
    {
        return $this->scopeConfig;
    }

    public function getStoreManager(): StoreManagerInterface
    {
        return $this->storeManager;
    }
}
