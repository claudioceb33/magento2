<?php

namespace Swissup\SeoHtmlSitemap\Model\Link\Source;

use \Magento\Framework\Data\OptionSourceInterface;
use \Swissup\SeoHtmlSitemap\Model\Link;

class IsActive implements OptionSourceInterface
{
    /**
     * @var \Swissup\SeoHtmlSitemap\Model\Link
     */
    protected $link;

    public function __construct(Link $link)
    {
        $this->link = $link;
    }
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->link->getAvailableStatuses();
        $options = [];

        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }

        return $options;
    }
}
