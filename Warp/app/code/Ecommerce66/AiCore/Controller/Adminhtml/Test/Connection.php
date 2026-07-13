<?php
declare(strict_types=1);

namespace Ecommerce66\AiCore\Controller\Adminhtml\Test;

use Ecommerce66\AiCore\Helper\Connect;
use Ecommerce66\AiCore\Helper\Data as ConfigHelper;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;

class Connection extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Ecommerce66_AiCore::config';

    private JsonFactory $resultJsonFactory;
    private Connect $connect;
    private ConfigHelper $configHelper;
    private StoreManagerInterface $storeManager;

    /**
     * Connection constructor.
     *
     * @param Context               $context
     * @param JsonFactory           $resultJsonFactory
     * @param Connect               $connect
     * @param ConfigHelper          $configHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Connect $connect,
        ConfigHelper $configHelper,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->connect           = $connect;
        $this->configHelper      = $configHelper;
        $this->storeManager      = $storeManager;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            // Scope handling opcional: podrías leer 'website'/'store' de request y mapear a $storeId
            $storeId = 0;

            if (!$this->configHelper->isEnabled($storeId)) {
                return $result->setData([
                    'success' => false,
                    'message' => __('AiCore is disabled.')
                ]);
            }

            // Llamada al endpoint de salud (GET). Si el endpoint requiere headers especiales, Connect ya los setea.
            $response = $this->connect->callHealth([], [], $storeId);

            // Consideramos 200-299 como éxito y permitimos que el body sea JSON o string.
            if ($response['status'] >= 200 && $response['status'] < 300) {
                $msg = is_array($response['body']) ? json_encode($response['body']) : (string)$response['body'];
                return $result->setData([
                    'success' => true,
                    'message' => $msg ?: __('Connection OK.')
                ]);
            }

            return $result->setData([
                'success' => false,
                'message' => __(
                    'HTTP %1. Body: %2',
                    $response['status'],
                    mb_strimwidth((string)$response['raw'], 0, 500, '...')
                )
            ]);
        } catch (\Throwable $e) {
            return $result->setData([
                'success' => false,
                'message' => __('Exception: %1', $e->getMessage())
            ]);
        }
    }
}
