<?php
declare(strict_types=1);

namespace Ecommerce66\AiContent\Helper;

use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Ecommerce66\AiCore\Helper\Data as AiCoreHelper;

class ContentGenerator
{
    private Curl $httpClient;
    private LoggerInterface $logger;
    private SerializerInterface $serializer;
    private ScopeConfigInterface $scopeConfig;
    private AiCoreHelper $aiCoreHelper;

    public function __construct(
        Curl $httpClient,
        LoggerInterface $logger,
        SerializerInterface $serializer,
        ScopeConfigInterface $scopeConfig,
        AiCoreHelper $aiCoreHelper
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->scopeConfig = $scopeConfig;
        $this->aiCoreHelper = $aiCoreHelper;
    }

    /**
     * Call AI content generation endpoint
     * @param string $target
     * @param string $userPromptJson
     * @return array ['success'=>bool,'content'=>string,'raw'=>array]
     */
    public function generate(string $target, string $userPromptJson): array
    {
        return $this->generateCustom($target, $target, $userPromptJson);
    }

    /**
     * Call AI content generation with custom prompt and response format targets
     * @param string $promptTarget Target for system prompt (from admin config)
     * @param string $responseFormatTarget Target for response format (from config.xml)
     * @param string $userPromptJson
     * @return array ['success'=>bool,'content'=>string,'raw'=>array]
     */
    public function generateCustom(string $promptTarget, string $responseFormatTarget, string $userPromptJson): array
    {
        $apiKey = $this->aiCoreHelper->getApiKey();
        $clientCode = $this->aiCoreHelper->getCode();
        $baseUrl = $this->aiCoreHelper->getBaseUrl();
        
        // Get system prompts from admin configuration (editable)
        $systemPrompts = [
            'cms' => (string)$this->scopeConfig->getValue('ecommerce66_ai/content/prompts/cms', ScopeInterface::SCOPE_STORE),
            'category' => (string)$this->scopeConfig->getValue('ecommerce66_ai/content/prompts/category', ScopeInterface::SCOPE_STORE),
            'product' => (string)$this->scopeConfig->getValue('ecommerce66_ai/content/prompts/product', ScopeInterface::SCOPE_STORE),
            'seo' => (string)$this->scopeConfig->getValue('ecommerce66_ai/content/prompts/seo', ScopeInterface::SCOPE_STORE),
        ];

        $systemPrompt = $systemPrompts[$promptTarget] ?? '';
        
        // Get response format instructions from config.xml (NOT editable from admin)
        $responseFormats = [
            'cms' => (string)$this->scopeConfig->getValue('ecommerce66_ai/content/response_format/cms', ScopeInterface::SCOPE_STORE),
            'category' => (string)$this->scopeConfig->getValue('ecommerce66_ai/content/response_format/category', ScopeInterface::SCOPE_STORE),
            'product' => (string)$this->scopeConfig->getValue('ecommerce66_ai/content/response_format/product', ScopeInterface::SCOPE_STORE),
            'seo' => (string)$this->scopeConfig->getValue('ecommerce66_ai/content/response_format/seo', ScopeInterface::SCOPE_STORE),
            'short_description' => (string)$this->scopeConfig->getValue('ecommerce66_ai/content/response_format/short_description', ScopeInterface::SCOPE_STORE),
        ];
        
        $responseFormat = $responseFormats[$responseFormatTarget] ?? '';
        
        // Append response format to system prompt
        if (!empty($responseFormat) && !empty($systemPrompt)) {
            $systemPrompt .= "\n\n" . $responseFormat;
        }
        
        // Debug: Log the system prompt being sent
        $this->logger->info('AI System Prompt for prompt_target: ' . $promptTarget . ', response_format_target: ' . $responseFormatTarget, ['prompt' => $systemPrompt]);

        $payload = [
            'target' => $promptTarget, // Use prompt target for AI endpoint
            'system_prompt' => $systemPrompt,
            'user_prompt' => $userPromptJson,
            'client_code' => $clientCode,
        ];

        $url = $baseUrl . '/rest/v1/content/generate';

        try {
            $this->httpClient->addHeader('Content-Type', 'application/json');
            if ($apiKey) {
                $this->httpClient->addHeader('X-API-Key', $apiKey);
            }
            $this->httpClient->post($url, $this->serializer->serialize($payload));
            $status = $this->httpClient->getStatus();
            $response = $this->httpClient->getBody();
            $decoded = [];
            try {
                $decoded = $this->serializer->unserialize($response);
            } catch (\Exception $e) {
                $decoded = ['raw' => $response];
            }

            if ($status >= 200 && $status < 300) {
                return ['success' => true, 'content' => $decoded['content'] ?? ($decoded['result'] ?? $response), 'raw' => $decoded];
            }

            return ['success' => false, 'content' => '', 'raw' => $decoded, 'status' => $status];
        } catch (\Exception $e) {
            $this->logger->critical('AI Content generation error: ' . $e->getMessage());
            return ['success' => false, 'content' => '', 'error' => $e->getMessage()];
        }
    }
}
