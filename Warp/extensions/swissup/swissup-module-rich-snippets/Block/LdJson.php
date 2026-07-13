<?php

namespace Swissup\RichSnippets\Block;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

abstract class LdJson extends Template
{
    /**
     * @var string
     */
    protected $_template = 'ld-json.phtml';

    /**
     * Get JSON string for Linking Data
     *
     * @return string
     */
    abstract public function getLdJson();

    /**
     * Prepare JSON string from array
     *
     * @param  array  $array
     * @return string
     */
    public function prepareJsonString(array $array)
    {
        $options = JSON_UNESCAPED_SLASHES;
        $isMinify = $this->getStoreConfig('richsnippets/general/minify');
        if (!$isMinify) {
            $options = $options | JSON_PRETTY_PRINT;
        }

        $json = json_encode($array, $options);

        return $isMinify ? $json : "\n$json\n";
    }

    /**
     * Create new array using $keyMap from $originalArray
     *
     * @param  array  $keyMap
     * @param  array  $originalArray
     * @return array
     */
    public function remapArray(array $keyMap, array $originalArray)
    {
        $newArray = [];
        foreach ($keyMap as $oldKey => $newKey) {
            if (isset($originalArray[$oldKey])) {
                $newArray[$newKey] = $originalArray[$oldKey];
            }
        }

        return $newArray;
    }

    /**
     * Get cofig value for current store level
     *
     * @param  string $key
     * @return string
     */
    public function getStoreConfig($key)
    {
        return $this->_scopeConfig->getValue($key,ScopeInterface::SCOPE_STORE);
    }
}
