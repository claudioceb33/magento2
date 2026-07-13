<?php
namespace Swissup\RichSnippets\Plugin;

use Swissup\RichSnippets\Model\Config\Source\StructuredDataFormat;

abstract class AbstractPlugin
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ){
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check is microdata disabled
     *
     * @return boolean
     */
    public function isMicrodataDisabled()
    {
        $moduleEnabled = $this->scopeConfig->getValue(
            'richsnippets/general/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $dataFormat = $this->scopeConfig->getValue(
            'richsnippets/general/product_format',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $moduleEnabled && ($dataFormat != StructuredDataFormat::MICRODATA);
    }
}
