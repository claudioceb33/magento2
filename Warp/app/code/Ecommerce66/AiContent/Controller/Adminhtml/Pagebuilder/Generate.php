<?php
declare(strict_types=1);

namespace Ecommerce66\AiContent\Controller\Adminhtml\Pagebuilder;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Ecommerce66\AiContent\Helper\ContentGenerator;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Cms\Api\PageRepositoryInterface;

class Generate extends Action
{
    const ADMIN_RESOURCE = 'Ecommerce66_AiCore::config';

    private JsonFactory $jsonFactory;
    private ContentGenerator $generator;
    private ProductRepositoryInterface $productRepository;
    private CategoryRepositoryInterface $categoryRepository;
    private PageRepositoryInterface $pageRepository;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        ContentGenerator $generator,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        PageRepositoryInterface $pageRepository
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->generator = $generator;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->pageRepository = $pageRepository;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        
        try {
            $contentType = $this->getRequest()->getParam('content_type', 'custom');
            $prompt = $this->getRequest()->getParam('prompt');
            $context = $this->getRequest()->getParam('context', '');
            
            // Get entity context from request (product_id, category_id, page_id)
            $entityId = $this->getRequest()->getParam('entity_id');
            $entityType = $this->getRequest()->getParam('entity_type');
            
            if (!$prompt) {
                return $result->setData([
                    'success' => false,
                    'message' => __('Prompt is required')
                ]);
            }

            // Build enhanced user prompt with clear instructions
            $systemInstructions = $this->getSystemInstructions($contentType);
            
            // Get entity context if available
            $entityContext = $this->getEntityContext($entityType, $entityId);
            
            $userPromptText = $systemInstructions . "\n\n" . $prompt;
            
            if (!empty($entityContext)) {
                $userPromptText .= "\n\n" . $entityContext;
            }
            
            if (!empty($context)) {
                $userPromptText .= "\n\nContexto adicional: " . $context;
            }
            
            $userPromptData = [
                'content_type' => $contentType,
                'prompt' => $userPromptText
            ];
            
            $userPrompt = json_encode($userPromptData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            // Determine target based on content type
            $target = $this->getTargetForContentType($contentType);
            
            // Generate content
            $resp = $this->generator->generate($target, $userPrompt);

            if ($resp['success']) {
                $content = $resp['content'] ?? '';

                if (!$content) {
                    $content = $this->resolveContentFromRaw($resp['raw'] ?? null);
                }

                if (!$content) {
                    return $result->setData([
                        'success' => false,
                        'message' => __('The AI service did not return any content. Try refining your prompt and retry.')
                    ]);
                }

                return $result->setData([
                    'success' => true,
                    'content' => $content
                ]);
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
     * Get target for content type
     */
    private function getTargetForContentType(string $contentType): string
    {
        $mapping = [
            'product_description' => 'product',
            'category_description' => 'category',
            'marketing_copy' => 'cms',
            'blog_post' => 'cms',
            'custom' => 'cms'
        ];
        
        return $mapping[$contentType] ?? 'cms';
    }

    /**
     * Get system instructions based on content type
     */
    private function getSystemInstructions(string $contentType): string
    {
        $instructions = [
            'product_description' => 'Genera ÚNICAMENTE el contenido de la descripción del producto solicitada. No incluyas saludos, explicaciones ni preguntas. Responde directamente con el texto de la descripción en formato HTML. Usa etiquetas <p>, <strong>, <ul> y <li> según sea apropiado.',
            
            'category_description' => 'Genera ÚNICAMENTE el contenido de la descripción de categoría solicitada. No incluyas saludos ni preguntas. Responde directamente con el texto en formato HTML.',
            
            'marketing_copy' => 'Genera ÚNICAMENTE el contenido de marketing solicitado. No incluyas saludos ni explicaciones. Responde directamente con el texto persuasivo en formato HTML.',
            
            'blog_post' => 'Genera ÚNICAMENTE el contenido del artículo de blog solicitado. No incluyas saludos. Responde directamente con el artículo completo en formato HTML con títulos, párrafos y listas.',
            
            'custom' => 'Genera ÚNICAMENTE el contenido solicitado. No incluyas saludos, explicaciones ni preguntas adicionales. Responde directamente con el contenido en formato HTML.'
        ];
        
        return $instructions[$contentType] ?? $instructions['custom'];
    }

    /**
     * Get entity context information
     */
    private function getEntityContext(?string $entityType, ?string $entityId): string
    {
        if (empty($entityType) || empty($entityId)) {
            return '';
        }

        try {
            switch ($entityType) {
                case 'product':
                    $product = $this->productRepository->getById($entityId);
                    return sprintf(
                        "CONTEXTO DEL PRODUCTO:\n" .
                        "Nombre: %s\n" .
                        "SKU: %s\n" .
                        "Descripción corta actual: %s\n" .
                        "Precio: %s",
                        $product->getName(),
                        $product->getSku(),
                        strip_tags($product->getShortDescription() ?? 'Sin descripción'),
                        $product->getPrice()
                    );

                case 'category':
                    $category = $this->categoryRepository->get($entityId);
                    return sprintf(
                        "CONTEXTO DE LA CATEGORÍA:\n" .
                        "Nombre: %s\n" .
                        "Descripción actual: %s",
                        $category->getName(),
                        strip_tags($category->getDescription() ?? 'Sin descripción')
                    );

                case 'cms_page':
                    $page = $this->pageRepository->getById($entityId);
                    return sprintf(
                        "CONTEXTO DE LA PÁGINA CMS:\n" .
                        "Título: %s\n" .
                        "Identificador: %s",
                        $page->getTitle(),
                        $page->getIdentifier()
                    );

                default:
                    return '';
            }
        } catch (\Exception $e) {
            // Log error but don't fail the request
            return '';
        }
    }

    /**
     * Try to extract HTML/text content from the raw response payload
     *
     * @param mixed $raw
     */
    private function resolveContentFromRaw($raw): string
    {
        if (is_string($raw)) {
            return $this->normalizeGeneratedContent($raw);
        }

        if (!is_array($raw)) {
            return '';
        }

        if (!empty($raw['content'])) {
            return (string)$raw['content'];
        }

        if (!empty($raw['result'])) {
            return (string)$raw['result'];
        }

        if (!empty($raw['message'])) {
            return $this->normalizeGeneratedContent($raw['message']);
        }

        if (!empty($raw['raw'])) {
            return $this->normalizeGeneratedContent($raw['raw']);
        }

        return '';
    }

    /**
     * Decode JSON payloads and fallback to plain text when needed
     */
    private function normalizeGeneratedContent(string $message): string
    {
        $decoded = json_decode($message, true);

        if (is_array($decoded)) {
            foreach (['content', 'message', 'description', 'text', 'html'] as $field) {
                if (!empty($decoded[$field])) {
                    return (string)$decoded[$field];
                }
            }
        }

        return trim($message);
    }
}
