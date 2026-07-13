<?php
declare(strict_types=1);

namespace Ecommerce66\AiLlmSearch\Model\Config\Backend;

use Ecommerce66\AiCore\Helper\Connect;
use Ecommerce66\AiLlmSearch\Helper\Config as WidgetConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class SellerPersona extends Value
{
    private Connect $connect;
    private WidgetConfig $config;
    private StoreManagerInterface $storeManager;
    private LoggerInterface $logger;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        Connect $connect,
        WidgetConfig $widgetConfig,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->connect = $connect;
        $this->config = $widgetConfig;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Load the current persona value from the API before rendering in admin
     */
    public function afterLoad()
    {
        parent::afterLoad();
        
        // Solo cargar desde API si el campo está vacío o es la primera vez
        $currentValue = $this->getValue();
        if ($currentValue === null || $currentValue === '') {
            $this->loadPersonaFromApi();
        }
        
        return $this;
    }

    /**
     * Before saving, update the seller persona via API
     */
    public function beforeSave()
    {
        $storeId = $this->resolveStoreId();
        $sellerId = $this->config->getDefaultSellerId($storeId);

        if ($sellerId === null) {
            throw new LocalizedException(
                __('No seller configured. Please select a Default Seller before updating persona.')
            );
        }

        $newPersona = trim((string)$this->getValue());
        
        try {
            $this->updateSellerPersona($sellerId, $newPersona, $storeId);
        } catch (\Throwable $e) {
            throw new LocalizedException(
                __('Failed to update seller persona: %1', $e->getMessage())
            );
        }

        return parent::beforeSave();
    }

    /**
     * Load the current persona from the API
     */
    private function loadPersonaFromApi(): void
    {
        try {
            $storeId = $this->resolveStoreId();
            $sellerId = $this->config->getDefaultSellerId($storeId);

            if ($sellerId === null) {
                return;
            }

            $response = $this->connect->request(
                'GET',
                sprintf('/sellers/%d', $sellerId),
                null,
                [],
                $storeId
            );

            $status = (int)($response['status'] ?? 0);
            if ($status >= 200 && $status < 300) {
                $body = $response['body'] ?? [];
                $persona = $body['persona'] ?? '';
                
                if (is_string($persona) && $persona !== '') {
                    $this->setValue($persona);
                }
            }
        } catch (\Throwable $e) {
            $this->logger->warning('Unable to load seller persona from API', [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update seller persona via PUT /sellers/{id}
     */
    private function updateSellerPersona(int $sellerId, string $persona, int $storeId): void
    {
        $payload = [
            'persona' => $persona !== '' ? $persona : null,
        ];

        try {
            $response = $this->connect->request(
                'PUT',
                sprintf('/sellers/%d', $sellerId),
                $payload,
                [],
                $storeId
            );

            $status = (int)($response['status'] ?? 0);
            if ($status < 200 || $status >= 300) {
                $this->logger->warning('Unable to update seller persona', [
                    'seller_id' => $sellerId,
                    'status' => $status,
                    'raw' => $response['raw'] ?? '',
                ]);
                throw new LocalizedException(
                    __('Copilot seller update failed (HTTP %1). See logs for details.', $status)
                );
            }
        } catch (\Throwable $e) {
            $this->logger->error('Error updating seller persona', [
                'seller_id' => $sellerId,
                'exception' => $e->getMessage(),
            ]);
            throw new LocalizedException(
                __('Could not update seller persona: %1', $e->getMessage())
            );
        }
    }

    private function resolveStoreId(): int
    {
        $scope = $this->getScope();
        $scopeId = (int)$this->getScopeId();

        if ($scope === 'stores') {
            return $scopeId;
        }

        if ($scope === 'websites') {
            try {
                return (int)$this->storeManager->getWebsite($scopeId)->getDefaultStore()->getId();
            } catch (NoSuchEntityException $e) {
                return 0;
            }
        }

        return 0;
    }
}
