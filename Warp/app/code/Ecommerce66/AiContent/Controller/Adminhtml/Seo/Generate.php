<?php
declare(strict_types=1);

namespace Ecommerce66\AiContent\Controller\Adminhtml\Seo;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Ecommerce66\AiContent\Helper\ContentGenerator;
use Magento\Framework\App\Request\Http;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class Generate extends Action
{
    const ADMIN_RESOURCE = 'Ecommerce66_AiCore::config';

    private JsonFactory $jsonFactory;
    private ContentGenerator $generator;
    private Http $request;
    private CategoryRepositoryInterface $categoryRepository;
    private ProductRepositoryInterface $productRepository;
    private PageRepositoryInterface $pageRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        ContentGenerator $generator,
        Http $request,
        CategoryRepositoryInterface $categoryRepository,
        ProductRepositoryInterface $productRepository,
        PageRepositoryInterface $pageRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->generator = $generator;
        $this->request = $request;
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->pageRepository = $pageRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        
        try {
            $entityType = $this->getRequest()->getParam('entity_type');
            $entityId = $this->getRequest()->getParam('entity_id');
            $contentType = $this->getRequest()->getParam('content_type', 'seo'); // seo, short_description, description, etc.
            
            if (!$entityType || !$entityId) {
                return $result->setData([
                    'success' => false,
                    'message' => __('Missing entity type or ID')
                ]);
            }

            // Load complete entity data
            $entityData = $this->loadEntityData($entityType, (int)$entityId);
            
            if (!$entityData) {
                return $result->setData([
                    'success' => false,
                    'message' => __('Could not load entity data')
                ]);
            }

            // Convert to JSON for AI prompt
            $userPrompt = json_encode($entityData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            // Determine which prompts to use based on content_type
            $promptTarget = $this->getPromptTarget($entityType, $contentType);
            $responseFormatTarget = $contentType;

            $resp = $this->generator->generateCustom($promptTarget, $responseFormatTarget, $userPrompt);

            if ($resp['success']) {
                // Get the message content directly from raw
                $messageContent = $resp['raw']['message'] ?? '';
                
                // Try to decode the JSON message directly
                $parsed = [];
                if (!empty($messageContent)) {
                    $decoded = json_decode($messageContent, true);
                    if ($decoded && is_array($decoded)) {
                        $parsed = $decoded;
                    }
                }
                
                return $result->setData(array_merge([
                    'success' => true
                ], $parsed));
            }

            return $result->setData([
                'success' => false,
                'message' => $resp['error'] ?? 'Generation failed'
            ]);

        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get prompt target based on entity type and content type
     */
    private function getPromptTarget(string $entityType, string $contentType): string
    {
        // Map content types to their prompt source
        $mapping = [
            'seo' => 'seo',
            'short_description' => 'product',
            'description' => 'product',
            'content' => $entityType, // cms or category
        ];
        
        return $mapping[$contentType] ?? $contentType;
    }

    /**
     * Load complete entity data based on type and ID
     */
    private function loadEntityData(string $entityType, int $entityId): ?array
    {
        try {
            switch ($entityType) {
                case 'category':
                    return $this->loadCategoryData($entityId);
                case 'product':
                    return $this->loadProductData($entityId);
                case 'cms_page':
                    return $this->loadCmsPageData($entityId);
                default:
                    return null;
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Load complete category data
     */
    private function loadCategoryData(int $categoryId): array
    {
        $category = $this->categoryRepository->get($categoryId);
        
        $data = [
            'entity_type' => 'category',
            'id' => $category->getId(),
            'name' => $category->getName(),
            'is_active' => $category->getIsActive(),
            'path' => $category->getPath(),
            'level' => $category->getLevel(),
        ];
        
        // Add custom attributes
        $customAttributes = $this->extractCustomAttributes($category);
        $data = array_merge($data, $customAttributes);
        
        return $data;
    }

    /**
     * Load complete product data
     */
    private function loadProductData(int $productId): array
    {
        $product = $this->productRepository->getById($productId);
        
        $data = [
            'entity_type' => 'product',
            'id' => $product->getId(),
            'sku' => $product->getSku(),
            'name' => $product->getName(),
            'type_id' => $product->getTypeId(),
            'price' => $product->getPrice(),
            'weight' => $product->getWeight(),
            'status' => $product->getStatus(),
            'visibility' => $product->getVisibility(),
            'attribute_set_id' => $product->getAttributeSetId(),
        ];
        
        // Add custom attributes (includes description, url_key, meta fields, etc.)
        $customAttributes = $this->extractCustomAttributes($product);
        $data = array_merge($data, $customAttributes);
        
        return $data;
    }

    /**
     * Load complete CMS page data
     */
    private function loadCmsPageData(int $pageId): array
    {
        $page = $this->pageRepository->getById($pageId);
        
        return [
            'entity_type' => 'cms_page',
            'id' => $page->getId(),
            'identifier' => $page->getIdentifier(),
            'title' => $page->getTitle(),
            'content' => $page->getContent(),
            'meta_title' => $page->getMetaTitle(),
            'meta_keywords' => $page->getMetaKeywords(),
            'meta_description' => $page->getMetaDescription(),
            'is_active' => $page->isActive(),
            'page_layout' => $page->getPageLayout(),
            'content_heading' => $page->getContentHeading()
        ];
    }

    /**
     * Extract custom attributes from entity
     */
    private function extractCustomAttributes($entity): array
    {
        $customAttributes = [];
        
        if (method_exists($entity, 'getCustomAttributes')) {
            foreach ($entity->getCustomAttributes() as $attribute) {
                $customAttributes[$attribute->getAttributeCode()] = $attribute->getValue();
            }
        }
        
        return $customAttributes;
    }
}
