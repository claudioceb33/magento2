<?php
declare(strict_types=1);

namespace Ecommerce66\AiSearch\Helper;

use Ecommerce66\AiCore\Helper\Data as CoreData;
use Ecommerce66\AiCore\Helper\Connect;
use Magento\Store\Model\ScopeInterface;

class Data extends CoreData
{
    public const XML_PATH_SECTION_SEARCH   = 'ecommerce66_ai/search/';
    public const XML_PATH_ENDPOINT_SEARCH  = self::XML_PATH_SECTION_SEARCH . 'endpoint_search';
    public const XML_PATH_SEARCH_TYPE      = self::XML_PATH_SECTION_SEARCH . 'search_type';
    public const XML_PATH_SEARCH_ITEMS     = self::XML_PATH_SECTION_SEARCH . 'search_items';

    public const XML_PATH_SLIDER_DESK      = self::XML_PATH_SECTION_SEARCH . 'slider_elements_desk';
    public const XML_PATH_SLIDER_TABLET    = self::XML_PATH_SECTION_SEARCH . 'slider_elements_tablet';
    public const XML_PATH_SLIDER_MOBILE    = self::XML_PATH_SECTION_SEARCH . 'slider_elements_mobile';
    public const XML_PATH_SEARCH_RESULT    = self::XML_PATH_SECTION_SEARCH . 'result_block';

    /**
     * @var Connect
     */
    private Connect $connect;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Connect                               $connect
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Connect $connect
    ) {
        parent::__construct($context);
        $this->connect = $connect;
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getSearchEndpoint(?int $storeId = 0): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_ENDPOINT_SEARCH, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return string[] Selected modes (multiselect)
     */
    public function getSearchType(?int $storeId = 0): array
    {
        $raw = (string)$this->scopeConfig->getValue(self::XML_PATH_SEARCH_TYPE, ScopeInterface::SCOPE_STORE, $storeId);
        $raw = trim($raw);
        return $raw === '' ? [] : explode(',', $raw);
    }

    /**
     * @param int|null $storeId
     *
     * @return int
     */
    public function getSearchItems(?int $storeId = 0): int
    {
        return max(1, (int)$this->scopeConfig->getValue(self::XML_PATH_SEARCH_ITEMS, ScopeInterface::SCOPE_STORE, $storeId));
    }

    /**
     * Calls AI search endpoint using AiCore Connect.
     *
     * @param string $query
     * @param int|null $limit
     * @param int|null $storeId
     * @return array{status:int,headers:array<string,string>,body:array<string,mixed>|string,raw:string,url:string}
     */
    public function searchAi(string $query, ?int $limit = null, ?int $storeId = 0): array
    {
        if (!$this->canShowResultBlock()) {
            return [];
        }

        $endpoint = $this->getSearchEndpoint($storeId);
        $payload  = [
            'query' => $query,
            'limit' => $limit ?? $this->getSearchItems($storeId),
            "offset" => 0,
            "use_hybrid" => true,
            "min_similarity" => 0.3,
            "model_version" => "string",
            "sort_by" => "relevance"
        ];

        // Se usa el cliente del core (headers/timeout/retries/credenciales)
        return $this->connect->request('POST', $endpoint, $payload, [], $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return int
     */
    public function getSliderElementsDesk(?int $storeId = 0): int
    {
        $v = (int)$this->scopeConfig->getValue(self::XML_PATH_SLIDER_DESK, ScopeInterface::SCOPE_STORE, $storeId);
        return $v > 0 ? $v : 5;
    }

    /**
     * @param int|null $storeId
     *
     * @return int
     */
    public function getSliderElementsTablet(?int $storeId = 0): int
    {
        $v = (int)$this->scopeConfig->getValue(self::XML_PATH_SLIDER_TABLET, ScopeInterface::SCOPE_STORE, $storeId);
        return $v > 0 ? $v : 2;
    }

    /**
     * @param int|null $storeId
     *
     * @return int
     */
    public function getSliderElementsMobile(?int $storeId = 0): int
    {
        $v = (int)$this->scopeConfig->getValue(self::XML_PATH_SLIDER_MOBILE, ScopeInterface::SCOPE_STORE, $storeId);
        return $v > 0 ? $v : 1;
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function canShowResultBlock(?int $storeId = 0): bool
    {
        if (!$this->isResultBlockEnabled($storeId)) {
            return false;
        }

        if ($this->isMagentoOnly($storeId)) {
            return false;
        }

        return true;
    }

    // NEW: feature toggle simple (alias del existente canShowResultBlock)
    public function isResultBlockEnabled(?int $storeId = 0): bool
    {
        return (bool)(int)$this->scopeConfig->getValue(self::XML_PATH_SEARCH_RESULT, ScopeInterface::SCOPE_STORE, $storeId);
    }

    // NEW: detecta "magento" como único modo
    public function isMagentoOnly(?int $storeId = 0): bool
    {
        $types = $this->getSearchType($storeId);
        return in_array('magento', $types, true) && count($types) === 1;
    }

    // NEW: detecta "fallback" como único modo
    public function isFallbackOnly(?int $storeId = 0): bool
    {
        $types = $this->getSearchType($storeId);
        return in_array('fallback', $types, true) && count($types) === 1;
    }

}
