<?php

namespace Swissup\Hreflang\Model\Config\Source;

class Website extends StoreView
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $websites = $this->storeManager->getWebsites();

        foreach ($websites as $website) {
            $options[] = [
                'value' => $website->getId(),
                'label' => $website->getName()
            ];
        }

        return $options;
    }
}
