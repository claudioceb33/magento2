<?php
declare(strict_types=1);

namespace Ecommerce66\AiLlmSearch\Controller\Adminhtml\Seller;

use Ecommerce66\AiCore\Helper\Connect;
use Ecommerce66\AiLlmSearch\Helper\Config;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class UpdatePersona extends Action
{
    public const ADMIN_RESOURCE = 'Ecommerce66_AiLlmSearch::config';

    private JsonFactory $resultJsonFactory;
    private Connect $connect;
    private Config $config;
    private StoreManagerInterface $storeManager;
    private LoggerInterface $logger;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Connect $connect,
        Config $config,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->connect = $connect;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        
        try {
            $storeId = (int)$this->getRequest()->getParam('store_id', 0);
            $persona = $this->getRequest()->getParam('persona', '');
            
            $sellerId = $this->config->getDefaultSellerId($storeId);
            
            if (!$sellerId) {
                return $result->setData([
                    'success' => false,
                    'message' => __('No seller configured. Please select a Default Seller first.')
                ]);
            }

            $payload = [
                'persona' => $persona !== '' ? $persona : null,
            ];

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
                    'response' => $response
                ]);
                
                return $result->setData([
                    'success' => false,
                    'message' => __('Failed to update seller persona (HTTP %1). Check logs for details.', $status)
                ]);
            }
            
            return $result->setData([
                'success' => true,
                'message' => __('Seller persona updated successfully!')
            ]);
            
        } catch (\Throwable $e) {
            $this->logger->error('Error updating seller persona', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $result->setData([
                'success' => false,
                'message' => __('Error: %1', $e->getMessage())
            ]);
        }
    }
}
