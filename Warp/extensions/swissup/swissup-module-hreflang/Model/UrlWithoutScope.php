<?php

namespace Swissup\Hreflang\Model;

class UrlWithoutScope extends \Magento\Framework\Url
{
    /**
     * {@inheritdoc}
     */
    public function getUrl($routePath = null, $routeParams = null)
    {
        // remove scope from URL
        // prevents long chain of parameters in store switcher
        unset($routeParams['_scope_to_url']);
        return parent::getUrl($routePath, $routeParams);
    }
}
