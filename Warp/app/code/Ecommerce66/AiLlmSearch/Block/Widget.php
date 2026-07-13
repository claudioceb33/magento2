<?php
declare(strict_types=1);

namespace Ecommerce66\AiLlmSearch\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Ecommerce66\AiLlmSearch\Helper\Config as WidgetConfig;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Widget\Block\BlockInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Block that exposes AiCore config values to the frontend via data-* attributes.
 */
class Widget extends Template implements BlockInterface
{
    /**
     * Default template for the widget rendering.
     *
     * @var string
     */
    protected $_template = 'Ecommerce66_AiLlmSearch::widget.phtml';

    private WidgetConfig $config;
    private SessionManagerInterface $sessionManager;

    public function __construct(
        Context $context,
        WidgetConfig $config,
        SessionManagerInterface $sessionManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->sessionManager = $sessionManager;
    }

    /**
     * Full endpoint used by frontend JS.
     */
    public function getEndpoint(): string
    {
        $storeId = $this->resolveStoreId();
        $path = trim((string)$this->getData('endpoint_path'));
        if ($path === '') {
            return $this->config->getCopilotUrl($storeId);
        }
        $base = $this->config->getBaseUrl($storeId);
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Return the proxy URL for frontend to call (hides external API URL and API key).
     */
    public function getProxyUrl(): string
    {
        return $this->getUrl('ecommerce66_llm/proxy');
    }

    public function isEnabled(): bool
    {
        return $this->config->isEnabled($this->resolveStoreId());
    }

    public function getSessionId(): string
    {
        return (string)$this->sessionManager->getSessionId();
    }

    public function getDefaultAction(): string
    {
        $action = (string)$this->getData('action');
        if (!in_array($action, ['search', 'compare', 'recommend'], true)) {
            return 'search';
        }
        return $action;
    }

    public function getDefaultQuery(): string
    {
        return trim((string)$this->getData('default_query') ?: '');
    }

    /**
     * Return API key from AiCore. Note: exposing API key in frontend is a security risk —
     * ideally a backend proxy should be used. This implementation follows the user's
     * requirement to make credentials available to the JS.
     */
    public function getApiKey(): string
    {
        return $this->config->getApiKey($this->resolveStoreId());
    }

    private function resolveStoreId(): int
    {
        try {
            return (int)$this->_storeManager->getStore()->getId();
        } catch (NoSuchEntityException $e) {
            return 0;
        }
    }
}
