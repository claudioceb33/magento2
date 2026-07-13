<?php

namespace Swissup\Hreflang\Plugin\App\Request;

use \Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Store\Model\Store;

class AbstractPlugin extends \Swissup\Hreflang\Plugin\AbstractPlugin
{
    /**
     * @var ReinitableConfigInterface
     */
    protected $config;

    /**
     * @var string
     */
    protected $originalDefaultScope;

    /**
     * @var string
     */
    protected $originalStoreScope;

    /**
     * @var Store
     */
    protected $currentStore;

    /**
     * @param ReinitableConfigInterface      $config
     * @param \Swissup\Hreflang\Helper\Store $helper
     */
    public function __construct(
        ReinitableConfigInterface $config,
        \Swissup\Hreflang\Helper\Store $helper
    ) {
        $this->config = $config;
        parent::__construct($helper);
    }

    /**
     * Force 'store in URL' in config
     */
    protected function forceStoreInUrl()
    {
        $currentStore = $this->helper->getCurrentStore();
        // Save original values.
        $this->originalDefaultScope = $this->config->getValue(Store::XML_PATH_STORE_IN_URL);
        $this->originalStoreScope = $this->config->getValue(
            Store::XML_PATH_STORE_IN_URL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $currentStore->getCode()
        );

        // Set store_in_url as true
        $this->config->setValue(Store::XML_PATH_STORE_IN_URL, '1');
        $this->config->setValue(
            Store::XML_PATH_STORE_IN_URL,
            '1',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $currentStore->getCode()
        );
    }

    /**
     * Restore config values after forcing 'store in URL'
     */
    protected function restoreConfig()
    {
        $currentStore = $this->helper->getCurrentStore();
        $this->config->setValue(
            Store::XML_PATH_STORE_IN_URL,
            $this->originalDefaultScope
        );
        $this->config->setValue(
            Store::XML_PATH_STORE_IN_URL,
            $this->originalStoreScope,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $currentStore->getCode()
        );
    }
}
