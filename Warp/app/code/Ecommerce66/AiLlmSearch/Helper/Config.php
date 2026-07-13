<?php
declare(strict_types=1);

namespace Ecommerce66\AiLlmSearch\Helper;

use Ecommerce66\AiCore\Helper\Data as BaseConfig;
use Magento\Store\Model\ScopeInterface;

/**
 * Helper extendiendo AiCore para exponer endpoint específico del copiloto.
 */
class Config extends BaseConfig
{
    public const XML_PATH_ENDPOINT_COPILOT = 'ecommerce66_ai/shopping/copilot_endpoint';
    private const DEFAULT_COPILOT_PATH = '/api/v1/copilot/search';
    public const XML_PATH_SHOPPING_SECTION = 'ecommerce66_ai/shopping/';
    public const XML_PATH_COPILOT_ENDPOINT = self::XML_PATH_SHOPPING_SECTION . 'copilot_endpoint';
    public const XML_PATH_SHOPPING_SELLER  = self::XML_PATH_SHOPPING_SECTION . 'seller_id';
    public const XML_PATH_COPILOT_TIMEOUT  = self::XML_PATH_SHOPPING_SECTION . 'copilot_timeout';

    public function getCopilotEndpoint(?int $storeId = 0): string
    {
        $path = (string)$this->scopeConfig->getValue(self::XML_PATH_ENDPOINT_COPILOT, ScopeInterface::SCOPE_STORE, $storeId);
        $path = trim($path);
        if ($path === '') {
            $path = self::DEFAULT_COPILOT_PATH;
        }
        return '/' . ltrim($path, '/');
    }

    public function getCopilotUrl(?int $storeId = 0): string
    {
        return rtrim($this->getBaseUrl($storeId), '/') . $this->getCopilotEndpoint($storeId);
    }

    public function getDefaultSellerId(?int $storeId = 0): ?int
    {
        $value = $this->getShoppingSellerId($storeId);
        return $value === null ? null : (int)$value;
    }
    public function getShoppingSellerId(?int $storeId = 0): ?int
    {
        $value = (string)$this->scopeConfig->getValue(self::XML_PATH_SHOPPING_SELLER, ScopeInterface::SCOPE_STORE, $storeId);
        $value = trim($value);
        return $value === '' ? null : (int)$value;
    }

    public function getCopilotTimeout(?int $storeId = 0): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_COPILOT_TIMEOUT, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
