<?php
declare(strict_types=1);

namespace Ecommerce66\AiSearch\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\RequestInterface;
use Ecommerce66\AiSearch\Helper\Data as AiSearchHelper;

class Results extends Template
{
    private RequestInterface $request;
    private AiSearchHelper $helper;

    private ?array $aiItems = null;
    private ?string $message = null;
    private int $aiTotal = 0;
    private int $aiTimeMs = 0;
    private array $aiSuggestions = [];

    /**
     * Results constructor.
     *
     * @param Template\Context $context
     * @param RequestInterface $request
     * @param AiSearchHelper   $helper
     * @param array            $data
     */
    public function __construct(
        Template\Context $context,
        RequestInterface $request,
        AiSearchHelper $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->request = $request;
        $this->helper  = $helper;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function shouldRender(): bool
    {
        if (!$this->helper->isEnabled()) {
            return false;
        }

        $types = (array) $this->helper->getSearchType();
        if (empty($types)) {
            return false;
        }

        $q = trim((string) $this->request->getParam('q', ''));
        if ($q === '') {
            return false;
        }

        if ($this->hasOnly($types, 'magento')) {
            return false;
        }

        if ($this->hasOnly($types, 'fallback') && $this->hasNativeResults()) {
            return false;
        }

        return true;
    }

    /**
     * Check if the multiselect contains only the given mode.
     *
     * @param string[] $types
     */
    private function hasOnly(array $types, string $mode): bool
    {
        return count($types) === 1 && in_array($mode, $types, true);
    }

    /**
     * Determine if native search already has results.
     */
    private function hasNativeResults(): bool
    {
        $resultBlock = $this->getLayout()->getBlock('search.result');
        $count = (is_object($resultBlock) && method_exists($resultBlock, 'getResultCount'))
            ? (int) $resultBlock->getResultCount()
            : 0;

        return $count > 0;
    }

    /**
     * @return int
     */
    public function getAiTotal(): int { return $this->aiTotal; }

    /**
     * @return int
     */
    public function getAiTimeMs(): int { return $this->aiTimeMs; }

    /**
     * @return array
     */
    public function getAiSuggestions(): array { return $this->aiSuggestions; }

    /**
     * @return array
     */
    public function getAiItems(): array
    {
        if ($this->aiItems !== null) {
            return $this->aiItems;
        }

        $this->aiItems = [];
        $q = trim((string)$this->request->getParam('q', ''));
        if ($q === '') {
            return $this->aiItems;
        }

        $limit  = $this->helper->getSearchItems();
        $resp   = $this->helper->searchAi($q, $limit);
        $status = (int)($resp['status'] ?? 0);

        if ($status >= 200 && $status < 300 && is_array($resp['body'])) {
            $body = $resp['body'];

            // contrato real
            $this->aiTotal      = (int)($body['total'] ?? 0);
            $this->aiTimeMs     = (int)($body['time_ms'] ?? 0);
            $this->aiSuggestions= is_array($body['related_suggestions'] ?? null) ? $body['related_suggestions'] : [];

            $items = $body['results'] ?? [];
            if (is_array($items)) {
                // cada item es un “product-like”
                $this->aiItems = array_slice($items, 0, $limit);
            }

            return $this->aiItems;
        }

        $this->message = is_string($resp['body'] ?? null) ? $resp['body'] : ($resp['raw'] ?? 'AI search failed.');

        return [];
    }

    /**
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        $types = implode(', ', $this->helper->getSearchType());
        return __('AI Results (%1)', $types)->render();
    }

    /**
     * @return int
     */
    public function getMaxItems(): int
    {
        return $this->helper->getSearchItems();
    }
}
