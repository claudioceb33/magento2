<?php

namespace Swissup\SeoTemplates\Observer;

use Swissup\SeoTemplates\Helper\Data as Helper;

class SetFlagPreloadSeodata implements \Magento\Framework\Event\ObserverInterface
{
    const FLAG_NAME = 'seodata_allow_preload';

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var PreloadSeodata
     */
    private $preloader;

    /**
     * @param Helper         $helper
     * @param PreloadSeodata $preloader
     */
    public function __construct(
        Helper $helper,
        PreloadSeodata $preloader
    ) {
        $this->helper = $helper;
        $this->preloader = $preloader;
    }

    /**
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->isEnabled()) {
            $collection = $observer->getEvent()->getCollection();
            $collection->setFlag(self::FLAG_NAME, true);
            if ($collection->isLoaded()) {
                $this->preloader->execute($observer);
            }
        }
    }
}
