<?php
namespace Ceb\Gtm\Controller\Ga;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Ceb\Gtm\Model\GaDataFactory;
use Ceb\Gtm\Model\GaData as GaDataModel;
use Ceb\Gtm\Model\ResourceModel\GaData as GaDataResource;
use Ceb\Gtm\Helper\Data as GtmHelper;

class Capture extends Action
{
    /** @var JsonFactory */
    protected $resultJsonFactory;
    
    /** @var CheckoutSession */
    protected $checkoutSession;
    
    /** @var GaDataFactory */
    protected $gaDataFactory;
    
    /** @var GaDataResource */
    protected $gaDataResource;
    
    /** @var GtmHelper */
    protected $gtmHelper;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CheckoutSession $checkoutSession,
        GaDataFactory $gaDataFactory,
        GaDataResource $gaDataResource,
        GtmHelper $gtmHelper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->checkoutSession = $checkoutSession;
        $this->gaDataFactory = $gaDataFactory;
        $this->gaDataResource = $gaDataResource;
        $this->gtmHelper = $gtmHelper;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        
        if (!$this->gtmHelper->isServerSideEnabled()) {
            return $result->setData(['success' => false, 'message' => 'Server-side disabled']);
        }

        $clientId = $this->getRequest()->getParam('client_id');
        $sessionId = $this->getRequest()->getParam('session_id');
        $quoteId = $this->checkoutSession->getQuoteId();

        if (!$clientId || !$quoteId) {
            return $result->setData(['success' => false, 'message' => 'Missing data']);
        }

        try {
            $model = $this->gaDataFactory->create();
            $this->gaDataResource->load($model, $quoteId, 'quote_id');
            
            $model->setQuoteId($quoteId);
            $model->setClientId($clientId);
            $model->setSessionId($sessionId);
            if (!$model->getStatus() || $model->getStatus() === GaDataModel::STATUS_PENDING) {
                $model->setStatus(GaDataModel::STATUS_PENDING);
            }
            
            $this->gaDataResource->save($model);
            
            return $result->setData(['success' => true]);
        } catch (\Exception $e) {
            return $result->setData(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
