<?php
/**
 * Copyright © Ecommerce66. All rights reserved.
 */
declare(strict_types=1);

namespace Ecommerce66\AiSearch\Controller\Ajax;

use Ecommerce66\AiSearch\Helper\Data as AiSearchHelper;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * AI Query Controller
 * 
 * Handles user prompts and communicates with external AI service
 */
class Query implements HttpPostActionInterface
{
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var JsonFactory
     */
    private JsonFactory $jsonFactory;

    /**
     * @var AiSearchHelper
     */
    private AiSearchHelper $aiSearchHelper;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param RequestInterface $request
     * @param JsonFactory $jsonFactory
     * @param AiSearchHelper $aiSearchHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        JsonFactory $jsonFactory,
        AiSearchHelper $aiSearchHelper,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->jsonFactory = $jsonFactory;
        $this->aiSearchHelper = $aiSearchHelper;
        $this->logger = $logger;
    }

    /**
     * Execute action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $result = $this->jsonFactory->create();

        return $this->respondSafely($result);
    }

    /**
     * Centralises error handling so execute() stays minimal.
     */
    private function respondSafely(ResultInterface $result): ResultInterface
    {
        try {
            return $this->resolvePrompt($result);
        } catch (Throwable $exception) {
            return $this->handleException($exception, $result);
        }
    }

    /**
     * Decode and validate the prompt from the request payload.
     *
     * @return array{prompt: string, custom_endpoint: string|null}|null
     */
    private function extractPrompt(ResultInterface $result): ?array
    {
        $content = $this->request->getContent();
        $data = json_decode($content ?? '', true);

        if (!is_array($data)) {
            $result->setData([
                'success' => false,
                'message' => __('Invalid request payload.')
            ]);
            return null;
        }

        $prompt = isset($data['prompt']) ? trim((string)$data['prompt']) : '';

        if ($prompt === '') {
            $result->setData([
                'success' => false,
                'message' => __('Please provide a search query.')
            ]);
            return null;
        }

        $customEndpoint = isset($data['custom_endpoint']) ? trim((string)$data['custom_endpoint']) : null;

        return [
            'prompt' => $prompt,
            'custom_endpoint' => $customEndpoint
        ];
    }

    /**
     * Build the JSON response based on the AI service response payload.
     *
     * @param array $aiResponse
     */
    private function buildResultFromAiResponse(array $aiResponse, ResultInterface $result): ResultInterface
    {
        if (($aiResponse['status'] ?? 0) !== 200) {
            $this->logger->error('AI Service Error', [
                'status' => $aiResponse['status'] ?? null,
                'response' => $aiResponse['raw'] ?? null
            ]);

            return $result->setData([
                'success' => false,
                'message' => __('Unable to process your request. Please try again later.')
            ]);
        }

        $responseBody = $aiResponse['body'] ?? null;

        $skuList = $this->extractSkus($responseBody);
        if (!empty($skuList)) {
            $this->logger->debug('AI query SKUs extracted', ['skus' => $skuList]);
            return $result->setData([
                'success' => true,
                'skus' => $skuList
            ]);
        }

        $message = is_string($responseBody)
            ? $responseBody
            : ($responseBody['message'] ?? __('No products found for your query.'));

        return $result->setData([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Resolve prompt or bail out early when validation fails.
     */
    private function resolvePrompt(ResultInterface $result): ResultInterface
    {
        $promptData = $this->extractPrompt($result);

        if ($promptData === null) {
            return $result;
        }

        return $this->buildResponseForPrompt(
            $promptData['prompt'],
            $promptData['custom_endpoint'],
            $result
        );
    }

    /**
     * Execute the AI lookup and build the response payload.
     *
     * @param string $prompt
     * @param string|null $customEndpoint
     * @param ResultInterface $result
     */
    private function buildResponseForPrompt(
        string $prompt,
        ?string $customEndpoint,
        ResultInterface $result
    ): ResultInterface {
        $aiResponse = $this->aiSearchHelper->searchAi($prompt, null, 0, $customEndpoint);

        return $this->buildResultFromAiResponse($aiResponse, $result);
    }

    /**
     * Extract SKUs from the AI response body if available.
     *
     * @param mixed $responseBody
     * @return string[]
     */
    private function extractSkus($responseBody): array
    {
        if (!is_array($responseBody)) {
            return [];
        }

        if (isset($responseBody['skus']) && is_array($responseBody['skus'])) {
            return array_values(array_filter(array_map('strval', $responseBody['skus'])));
        }

        if (!isset($responseBody['results']) || !is_array($responseBody['results'])) {
            return [];
        }

        $skus = [];
        foreach ($responseBody['results'] as $item) {
            if (isset($item['sku'])) {
                $skus[] = (string)$item['sku'];
            }
        }

        return $skus;
    }

    /**
     * Handle errors consistently for logging and response payload.
     */
    private function handleException(Throwable $exception, ResultInterface $result): ResultInterface
    {
        $this->logger->critical('AI Query Controller Exception', [
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        return $result->setData([
            'success' => false,
            'message' => __('An error occurred while processing your request.')
        ]);
    }
}
