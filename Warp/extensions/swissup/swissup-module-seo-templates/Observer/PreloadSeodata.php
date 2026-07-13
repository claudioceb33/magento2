<?php

namespace Swissup\SeoTemplates\Observer;

use Swissup\SeoTemplates\Model\SeodataBuilder;
use Swissup\SeoTemplates\Model\Template;

class PreloadSeodata implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var SeodataBuilder
     */
    protected $seodataBuilder;

    /**
     * @param SeodataBuilder $seodataBuilder
     */
    public function __construct(
        SeodataBuilder $seodataBuilder
    ) {
        $this->seodataBuilder = $seodataBuilder;
    }

    /**
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $collection = $observer->getEvent()->getCollection();
        if ($collection->getFlag(SetFlagPreloadSeodata::FLAG_NAME)) {
            $this->seodataBuilder->preload(
                $collection->getColumnValues('entity_id'),
                Template::ENTITY_TYPE_PRODUCT
            );
            // unset flag
            $collection->getFlag(SetFlagPreloadSeodata::FLAG_NAME, null);
        }
    }
}
