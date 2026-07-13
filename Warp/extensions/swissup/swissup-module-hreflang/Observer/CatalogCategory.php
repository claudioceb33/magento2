<?php

namespace Swissup\Hreflang\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Swissup\Hreflang\Model\ResourceModel\Category as Resource;

class CatalogCategory implements ObserverInterface
{
    private Resource $resource;

    public function __construct(
        Resource $resource
    ) {
        $this->resource = $resource;
    }

    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        $category = $observer->getCategory();
        switch ($event->getName()) {
            case 'catalog_category_load_after':
                $this->resource->loadHreflangData($category);
                break;

            case 'catalog_category_prepare_save':
                $request = $observer->getRequest();
                $links = $request->getParam('hreflang_links', []);
                $category->setData(Resource::DATA_KEY_LINKS, $links);
                break;

            case 'catalog_category_save_after':
                $this->resource->saveHreflangData($category);
                break;
        }
    }
}
