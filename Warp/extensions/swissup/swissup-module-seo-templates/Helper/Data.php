<?php

namespace Swissup\SeoTemplates\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /**
     * Is SEO Templates module enabled
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'swissup_seotemplates/general/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is generated metadata forced
     *
     * @return boolean
     */
    public function isForced()
    {
        return $this->scopeConfig->isSetFlag(
            'swissup_seotemplates/general/force',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getOptimizeLength($seodataName)
    {
        return $this->scopeConfig->getValue(
            "swissup_seotemplates/optimize/{$seodataName}_length",
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getOptimizeEtc($seodataName)
    {
        return $this->scopeConfig->getValue(
            "swissup_seotemplates/optimize/{$seodataName}_etc",
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->_getRequest();
    }
}
