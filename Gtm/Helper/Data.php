<?php
namespace Ceb\Gtm\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\State;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_ENABLED = 'ceb_gtm/general/enabled';
    const XML_PATH_GTM_ID = 'ceb_gtm/general/gtm_id';
    const XML_PATH_BRAND_ATTRIBUTE = 'ceb_gtm/general/brand_attribute';
    const XML_PATH_CATEGORY_ATTRIBUTE = 'ceb_gtm/general/category_attribute';
    const XML_PATH_SERVER_SIDE_ENABLED = 'ceb_gtm/server_side/enabled';
    const XML_PATH_MEASUREMENT_ID = 'ceb_gtm/server_side/measurement_id';
    const XML_PATH_API_SECRET = 'ceb_gtm/server_side/api_secret';
    const XML_PATH_DEBUG_MODE = 'ceb_gtm/server_side/debug_mode';

    /**
     * @var array
     */
    protected $categoryCache = [];

    /**
     * @var array
     */
    protected $brandCache = [];

    /**
     * @var array
     */
    protected $productCategoryCache = [];

    /**
     * @var array
     */
    protected $attributeTextCache = [];

    /**
     * @var array
     */
    protected $attributeMetadataCache = [];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var State
     */
    protected $appState;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        State $appState
    ) {
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->encryptor = $encryptor;
        $this->appState = $appState;
        parent::__construct($context);
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    public function getGtmId(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_GTM_ID, ScopeInterface::SCOPE_STORE);
    }

    public function getBrandAttribute(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_BRAND_ATTRIBUTE, ScopeInterface::SCOPE_STORE);
    }

    public function getCategoryAttribute(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CATEGORY_ATTRIBUTE, ScopeInterface::SCOPE_STORE);
    }

    public function isServerSideEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SERVER_SIDE_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    public function getMeasurementId(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MEASUREMENT_ID, ScopeInterface::SCOPE_STORE);
    }

    public function getApiSecret(): ?string
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_API_SECRET, ScopeInterface::SCOPE_STORE);
        return $value ? $this->encryptor->decrypt($value) : null;
    }

    public function isDebugMode(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_DEBUG_MODE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Restrict informational logs to developer mode only.
     *
     * @return bool
     */
    public function canLogInfo(): bool
    {
        return $this->appState->getMode() === State::MODE_DEVELOPER;
    }

    public function getStoreCurrencyCode(): string
    {
        return (string) $this->storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
     * Get brand value from product
     * 
     * @param \Magento\Catalog\Model\Product $product
     * @return string|null
     */
    public function getProductBrand($product): ?string
    {
        $attributeCode = $this->getBrandAttribute();
        if (!$attributeCode || !$product) {
            return null;
        }

        $cacheKey = $this->buildProductAttributeCacheKey($product, $attributeCode);
        if (array_key_exists($cacheKey, $this->brandCache)) {
            return $this->brandCache[$cacheKey];
        }

        $value = $this->getAttributeTextValue($product, $attributeCode);

        $this->brandCache[$cacheKey] = $value ? (string) $value : null;

        return $this->brandCache[$cacheKey];
    }

    /**
     * Get category value from product
     * 
     * @param \Magento\Catalog\Model\Product $product
     * @return string|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getProductCategory($product): ?string
    {
        if (!$product) {
            return 'category';
        }

        $cacheKey = $this->buildProductCacheKey($product);
        if (array_key_exists($cacheKey, $this->productCategoryCache)) {
            return $this->productCategoryCache[$cacheKey];
        }

        $attributeCategory = $this->getCategoryFromAttribute($product);
        if ($attributeCategory !== null) {
            $this->productCategoryCache[$cacheKey] = $attributeCategory;
            return $this->productCategoryCache[$cacheKey];
        }

        $this->productCategoryCache[$cacheKey] = $this->getCategoryNameFromRepository($product);

        return $this->productCategoryCache[$cacheKey];
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string|null
     */
    protected function getCategoryFromAttribute($product): ?string
    {
        $attributeCode = $this->getCategoryAttribute();
        if (!$attributeCode) {
            return null;
        }

        $value = $this->getAttributeTextValue($product, $attributeCode);
        if (!$value) {
            return null;
        }

        if (is_array($value) && count($value) > 0) {
            return (string) $value[0];
        }

        return (string) $value;
    }

    /**
     * Extract attribute value (handles select/multiselect text fallback)
     * 
     * @param \Magento\Catalog\Model\Product $product
     * @param string $attributeCode
     * @return mixed
     */
    protected function getAttributeTextValue($product, $attributeCode)
    {
        $cacheKey = $this->buildProductAttributeCacheKey($product, $attributeCode);
        if (array_key_exists($cacheKey, $this->attributeTextCache)) {
            return $this->attributeTextCache[$cacheKey];
        }

        $value = $product->getData($attributeCode);

        if ($value && is_numeric($value)) {
            $attribute = $this->getAttributeMetadata($product, $attributeCode);
            if ($attribute && ($attribute->usesSource() || $attribute->getFrontendInput() === 'select')) {
                $value = $product->getAttributeText($attributeCode);
            }
        }

        $this->attributeTextCache[$cacheKey] = $value;

        return $this->attributeTextCache[$cacheKey];
    }

    /**
     * Fallback to reading the category name from the repository
     * 
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    protected function getCategoryNameFromRepository($product): string
    {
        $categoryIds = $product->getCategoryIds();
        if (!$categoryIds || !is_array($categoryIds) || count($categoryIds) === 0) {
            return 'category';
        }

        $categoryId = end($categoryIds);
        if (isset($this->categoryCache[$categoryId])) {
            return $this->categoryCache[$categoryId];
        }

        try {
            $storeId = $this->storeManager->getStore()->getId();
            $category = $this->categoryRepository->get($categoryId, $storeId);
            $name = $category->getName();
            $this->categoryCache[$categoryId] = $name;
            return $name;
        } catch (\Exception $e) {
            if ($this->canLogInfo()) {
                $this->_logger->error($e->getMessage());
            }
        }

        return 'category';
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param string $attributeCode
     * @return mixed
     */
    protected function getAttributeMetadata($product, string $attributeCode)
    {
        if (array_key_exists($attributeCode, $this->attributeMetadataCache)) {
            return $this->attributeMetadataCache[$attributeCode];
        }

        $this->attributeMetadataCache[$attributeCode] = $product->getResource()->getAttribute($attributeCode);

        return $this->attributeMetadataCache[$attributeCode];
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    protected function buildProductCacheKey($product): string
    {
        $productId = (int) $product->getId();
        if ($productId > 0) {
            return (string) $productId;
        }

        return spl_object_hash($product);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param string $attributeCode
     * @return string
     */
    protected function buildProductAttributeCacheKey($product, string $attributeCode): string
    {
        return $this->buildProductCacheKey($product) . '|' . $attributeCode;
    }
}
