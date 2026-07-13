<?php

namespace Swissup\SeoImages\Observer;

class SetFlagPreresolveFiles implements \Magento\Framework\Event\ObserverInterface
{
    const FLAG_NAME = 'seoimages_allow_preresolve_files';

    /**
     * @var \Swissup\SeoImages\Helper\Data
     */
    private $helper;

    /**
     * @var PreresolveFiles
     */
    private $preresolver;

    /**
     * @param \Swissup\SeoImages\Helper\Data $helper
     * @param PreresolveFiles                $preresolver
     */
    public function __construct(
        \Swissup\SeoImages\Helper\Data $helper,
        PreresolveFiles $preresolver
    ) {
        $this->helper = $helper;
        $this->preresolver = $preresolver;
    }

    /**
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->canChangeName()) {
            $collection = $observer->getEvent()->getCollection();
            $collection->setFlag(self::FLAG_NAME, true);
            if ($collection->isLoaded()) {
                $this->preresolver->execute($observer);
            }
        }
    }
}
