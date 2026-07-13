<?php

namespace Swissup\ProLabels\Observer;

class SetFlagPreloadLabels implements \Magento\Framework\Event\ObserverInterface
{
    const FLAG_NAME = 'prolabels_allow_preload';

    /**
     * @var PreloadLabels
     */
    private $preloader;

    public function __construct(
        PreloadLabels $preloader
    ) {
        $this->preloader = $preloader;
    }

    /**
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $collection = $observer->getCollection();
        $collection->setFlag(self::FLAG_NAME, true);
        if ($collection->isLoaded()) {
            $this->preloader->execute($observer);
        }
    }
}
