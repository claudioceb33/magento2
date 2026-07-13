<?php

namespace Swissup\Hreflang\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Swissup\Hreflang\Model\ResourceModel\Page as Resource;

class CmsPage implements ObserverInterface
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
        $page = $observer->getPage() ?: $observer->getObject();
        switch ($event->getName()) {
            case 'cms_page_load_after':
                $this->resource->loadHreflangData($page);
                break;

            case 'cms_page_prepare_save':
                $request = $observer->getRequest();
                $links = $request->getParam('hreflang_links', []);
                $page->setData(Resource::DATA_KEY_LINKS, $links);
                break;

            case 'cms_page_save_after':
                $this->resource->saveHreflangData($page);
                break;
        }
    }
}
