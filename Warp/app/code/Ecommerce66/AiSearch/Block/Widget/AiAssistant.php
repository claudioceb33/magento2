<?php
/**
 * Copyright © Ecommerce66. All rights reserved.
 */
declare(strict_types=1);

namespace Ecommerce66\AiSearch\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

/**
 * AI Product Recommendation Assistant Widget Block
 */
class AiAssistant extends Template implements BlockInterface
{
    /**
     * @var string
     */
    protected $_template = 'Ecommerce66_AiSearch::widget/ai_assistant.phtml';

    /**
     * Get widget title
     *
     * @return string
     */
    public function getTitle(): string
    {
        return (string)$this->getData('title');
    }

    /**
     * Get placeholder text for the search input
     */
    public function getPlaceholderText(): string
    {
        return $this->getData('placeholder_text') 
            ? (string)$this->getData('placeholder_text') 
            : __('Ask me about products...')->render();
    }

    /**
     * Retrieve related example phrases configured for the widget
     *
     * @return string[]
     */
    public function getRelatedExamples(): array
    {
        $rawExamples = $this->getData('related_examples');

        if (!is_string($rawExamples) || trim($rawExamples) === '') {
            return [];
        }

        $examples = array_filter(array_map('trim', explode(',', $rawExamples)), static function ($value) {
            return $value !== '';
        });

        return array_values($examples);
    }

    /**
     * Get URL for AI query controller
     *
     * @return string
     */
    public function getAiQueryUrl(): string
    {
        return $this->getUrl('aisearch/ajax/query', ['_secure' => true]);
    }

    /**
     * Get URL for product list controller
     *
     * @return string
     */
    public function getProductListUrl(): string
    {
        return $this->getUrl('aisearch/products/list', ['_secure' => true]);
    }

    /**
     * Get URL for recommendations controller
     */
    public function getRecommendationsUrl(): string
    {
        return $this->getUrl('aisearch/products/recommend', ['_secure' => true]);
    }

    public function shouldShowRecommendations(): bool
    {
        $value = $this->getData('show_recommendations');

        if ($value === null || $value === '') {
            return true;
        }

        if (is_bool($value)) {
            return $value;
        }

        $value = (string)$value;

        return in_array(strtolower($value), ['1', 'true', 'yes', 'si', 'sí'], true);
    }

    /**
     * Get custom search endpoint if configured in widget instance
     *
     * @return string
     */
    public function getCustomEndpoint(): string
    {
        return (string)$this->getData('custom_endpoint');
    }

    /**
     * Get unique widget ID for multiple instances
     *
     * @return string
     */
    public function getWidgetId(): string
    {
        return 'ai-widget-' . $this->getNameInLayout();
    }
}
