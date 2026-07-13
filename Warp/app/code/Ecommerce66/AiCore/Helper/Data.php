<?php
declare(strict_types=1);

namespace Ecommerce66\AiCore\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    public const XML_PATH_SECTION = 'ecommerce66_ai/core/';

    public const XML_PATH_ENABLED          = self::XML_PATH_SECTION . 'enabled';
    public const XML_PATH_LABEL            = self::XML_PATH_SECTION . 'label';
    public const XML_PATH_CODE             = self::XML_PATH_SECTION . 'code';
    public const XML_PATH_API_KEY          = self::XML_PATH_SECTION . 'api_key';
    public const XML_PATH_CLIENT_ID        = self::XML_PATH_SECTION . 'client_id';
    public const XML_PATH_BASE_URL         = self::XML_PATH_SECTION . 'base_url';
    public const XML_PATH_ENDPOINT_HEALTH  = self::XML_PATH_SECTION . 'endpoint_health';
    public const XML_PATH_TIMEOUT          = self::XML_PATH_SECTION . 'timeout';
    public const XML_PATH_RETRIES          = self::XML_PATH_SECTION . 'retries';
    public const XML_PATH_RETRY_DELAY      = self::XML_PATH_SECTION . 'retry_delay';

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isEnabled(?int $storeId = 0): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getLabel(?int $storeId = 0): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_LABEL, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getCode(?int $storeId = 0): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_CODE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getApiKey(?int $storeId = 0): string
    {
        // Stored encrypted automatically by Magento, returned decrypted here
        return (string)$this->scopeConfig->getValue(self::XML_PATH_API_KEY, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getClientId(?int $storeId = 0): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_CLIENT_ID, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getBaseUrl(?int $storeId = 0): string
    {
        return rtrim((string)$this->scopeConfig->getValue(self::XML_PATH_BASE_URL, ScopeInterface::SCOPE_STORE, $storeId), '/');
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getEndpointHealth(?int $storeId = 0): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_ENDPOINT_HEALTH, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return int
     */
    public function getTimeout(?int $storeId = 0): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_TIMEOUT, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return int
     */
    public function getRetries(?int $storeId = 0): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_RETRIES, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return int
     */
    public function getRetryDelayMs(?int $storeId = 0): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_RETRY_DELAY, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
