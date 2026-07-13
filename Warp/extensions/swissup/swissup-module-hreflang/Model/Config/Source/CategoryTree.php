<?php

namespace Swissup\Hreflang\Model\Config\Source;

use Swissup\Hreflang\Model\CategoryTree\Decorator;
use Swissup\Hreflang\Model\CategoryTree\Provider;

class CategoryTree implements \Magento\Framework\Data\OptionSourceInterface
{
    private Decorator $decorator;
    private Provider $provider;

    public function __construct(
        Decorator $decorator,
        Provider $provider
    ) {
        $this->decorator = $decorator;
        $this->provider = $provider;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $tree = $this->provider->provide();

        return empty($tree) ? [] : $this->decorator->decorate($tree);
    }
}
