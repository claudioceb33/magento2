<?php

namespace Swissup\SeoCanonical\Model\UrlMaker;

use Magento\Framework\DataObject;
use Magento\Framework\UrlFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

abstract class AbstractUrl
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var UrlFactory
     */
    protected $urlFactory;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlFactory           $urlFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        UrlFactory $urlFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->urlFactory = $urlFactory;
    }

    abstract public function getUrl(DataObject $entity): string;

    protected function getUrlFromAttribute(
        DataObject $entity,
        string $attributeCode
    ): string {
        $urlKey = $entity->getData($attributeCode);
        $url = $this->urlFactory->create()->setScope($entity->getStoreId());

        return (substr($urlKey, 0, 4) === 'http') ?
            $urlKey :
            $url->getUrl('', ['_direct' => $urlKey]);
    }
}
