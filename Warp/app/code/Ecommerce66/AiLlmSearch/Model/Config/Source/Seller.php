<?php
declare(strict_types=1);

namespace Ecommerce66\AiLlmSearch\Model\Config\Source;

use Ecommerce66\AiCore\Helper\Connect;
use Ecommerce66\AiLlmSearch\Helper\Config as WidgetConfig;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class Seller implements OptionSourceInterface
{
    private Connect $connect;
    private WidgetConfig $config;
    private RequestInterface $request;
    private StoreManagerInterface $storeManager;
    private LoggerInterface $logger;

    public function __construct(
        Connect $connect,
        WidgetConfig $config,
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->connect = $connect;
        $this->config = $config;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    public function toOptionArray(): array
    {
        $options = [
            ['value' => '', 'label' => __('-- Please Select --')]
        ];

        $storeId = $this->resolveStoreId();
        if (!$this->config->isEnabled($storeId)) {
            return $options;
        }

        return array_merge($options, $this->collectSellerOptions($storeId));
    }

    private function collectSellerOptions(int $storeId): array
    {
        try {
            $response = $this->fetchSellers($storeId);
            if ($response === []) {
                return [];
            }

            return $this->buildOptionsFromResponse($response);
        } catch (\Throwable $e) {
            $this->logger->error('Error retrieving sellers for Shopping Assistance config', [
                'message' => $e->getMessage(),
                'store_id' => $storeId,
            ]);
            return [];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchSellers(int $storeId): array
    {
        $payload = [];
        $clientId = $this->config->getClientId($storeId);
        if ($clientId !== '') {
            $payload['client_id'] = (int)$clientId;
        }

        $response = $this->connect->request('GET', '/sellers/', $payload, [], $storeId);
        $status = (int)($response['status'] ?? 0);
        $body = $response['body'] ?? [];

        if ($status >= 200 && $status < 300 && is_array($body)) {
            return $body;
        }

        $this->logger->warning('Unable to fetch sellers list for config', [
            'status' => $status,
            'raw' => $response['raw'] ?? '',
            'store_id' => $storeId,
        ]);

        return [];
    }

    /**
     * @param array<int, array<string, mixed>> $sellers
     */
    private function buildOptionsFromResponse(array $sellers): array
    {
        $options = [];
        foreach ($sellers as $seller) {
            if (!is_array($seller) || !isset($seller['id'])) {
                continue;
            }
            $id = (int)$seller['id'];
            $options[] = [
                'value' => (string)$id,
                'label' => $this->formatSellerLabel($seller, $id),
            ];
        }

        return $options;
    }

    /**
     * @param array<string, mixed> $seller
     */
    private function formatSellerLabel(array $seller, int $id): string
    {
        $name = '';
        if (isset($seller['name']) && is_string($seller['name']) && $seller['name'] !== '') {
            $name = $seller['name'];
        }

        $displayName = $name !== '' ? $name : (string)__('Seller #%1', $id);
        return sprintf('%s (ID %d)', $displayName, $id);
    }

    private function resolveStoreId(): int
    {
        $storeCode = $this->request->getParam('store');
        if ($storeCode) {
            try {
                return (int)$this->storeManager->getStore($storeCode)->getId();
            } catch (NoSuchEntityException $e) {
                return 0;
            }
        }

        $websiteCode = $this->request->getParam('website');
        if ($websiteCode) {
            try {
                return (int)$this->storeManager->getWebsite($websiteCode)->getDefaultStore()->getId();
            } catch (NoSuchEntityException $e) {
                return 0;
            }
        }

        return 0;
    }
}
