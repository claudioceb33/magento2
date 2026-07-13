<?php

namespace Swissup\SeoXmlSitemap\Model\Cms;

use Swissup\SeoCore\Model\Cms;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class GetUtilityPageIdentifiers extends \Magento\Cms\Model\GetUtilityPageIdentifiers
{
    /**
     * @var Cms
     */
    private $cms;

    /**
     * @param Cms                  $cms
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Cms $cms,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->cms = $cms;
        parent::__construct($scopeConfig);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $items = parent::execute();
        $items[] = $this->cms->getHomepageIdentifier();

        return array_unique($items);
    }
}
