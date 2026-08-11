<?php
namespace Ceb\Gtm\Plugin\Quote;

use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterface;
use Ceb\Gtm\Model\GaDataFactory;
use Ceb\Gtm\Model\GaData as GaDataModel;
use Ceb\Gtm\Model\ResourceModel\GaData as GaDataResource;
use Ceb\Gtm\Helper\Data as GtmHelper;
use Psr\Log\LoggerInterface;

class LinkOrderToGaData
{
    /** @var GaDataFactory */
    protected $gaDataFactory;
    
    /** @var GaDataResource */
    protected $gaDataResource;
    
    /** @var GtmHelper */
    protected $gtmHelper;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(
        GaDataFactory $gaDataFactory,
        GaDataResource $gaDataResource,
        GtmHelper $gtmHelper,
        LoggerInterface $logger
    ) {
        $this->gaDataFactory = $gaDataFactory;
        $this->gaDataResource = $gaDataResource;
        $this->gtmHelper = $gtmHelper;
        $this->logger = $logger;
    }

    /**
     * Link order_id to ga_data record after successful order placement
     *
     * @param QuoteManagement $subject
     * @param OrderInterface $order
     * @param Quote $quote
     * @return OrderInterface
     */
    public function afterSubmit(QuoteManagement $subject, OrderInterface $order, Quote $quote)
    {
        if (!$this->gtmHelper->isServerSideEnabled() || !$order->getEntityId()) {
            return $order;
        }

        try {
            $model = $this->gaDataFactory->create();
            $this->gaDataResource->load($model, $quote->getId(), 'quote_id');

            if ($model->getId()) {
                $model->setOrderId($order->getEntityId());
                $model->setOrderIncrementId($order->getIncrementId());
                $model->setStatus(GaDataModel::STATUS_PENDING);
                $this->gaDataResource->save($model);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error linking GA Data to Order: ' . $e->getMessage());
        }

        return $order;
    }
}
