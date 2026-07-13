<?php

namespace Swissup\ProLabelsConfigurableProduct\Observer;

use Swissup\ProLabelsConfigurableProduct\Model\ResourceModel\Label\Configurable as ResourceConfigurableLabels;

class LabelSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    private ResourceConfigurableLabels $resourceConfigurableLabels;

    public function __construct(
        ResourceConfigurableLabels $resourceConfigurableLabels
    ) {
        $this->resourceConfigurableLabels = $resourceConfigurableLabels;
    }

    /**
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $label = $observer->getLabel();
        $this->resourceConfigurableLabels->write($label);
    }
}
