<?php
namespace Ceb\Gtm\Cron;

use Magento\Sales\Api\OrderRepositoryInterface;
use Ceb\Gtm\Model\ResourceModel\GaData\CollectionFactory as GaDataCollectionFactory;
use Ceb\Gtm\Model\GA4\MeasurementProtocol;
use Ceb\Gtm\Model\GaData;
use Ceb\Gtm\Helper\Data as GtmHelper;
use Ceb\Gtm\Model\ResourceModel\GaData as GaDataResource;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class SendPurchases
{
    /** @var OrderRepositoryInterface */
    protected $orderRepository;

    /** @var GaDataCollectionFactory */
    protected $collectionFactory;

    /** @var MeasurementProtocol */
    protected $measurementProtocol;

    /** @var GtmHelper */
    protected $gtmHelper;

    /** @var GaDataResource */
    protected $gaDataResource;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        GaDataCollectionFactory $collectionFactory,
        MeasurementProtocol $measurementProtocol,
        GtmHelper $gtmHelper,
        GaDataResource $gaDataResource,
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->collectionFactory = $collectionFactory;
        $this->measurementProtocol = $measurementProtocol;
        $this->gtmHelper = $gtmHelper;
        $this->gaDataResource = $gaDataResource;
        $this->logger = $logger;
    }

    /**
     * Send pending purchases
     */
    public function execute()
    {
        if (!$this->gtmHelper->isServerSideEnabled()) {
            return;
        }

        $dateLimit = $this->getDateLimit();
        $collection = $this->getPendingCollection($dateLimit);
        $this->processCollection($collection);
        $this->cleanup($dateLimit);
    }

    protected function getDateLimit(): string
    {
        $days = 30;
        return date('Y-m-d H:i:s', strtotime("-$days days"));
    }

    protected function getPendingCollection(string $dateLimit)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('order_id', ['notnull' => true]);
        $collection->addFieldToFilter('sent_at', ['null' => true]);
        $collection->addFieldToFilter('status', ['eq' => GaData::STATUS_PENDING]);
        $collection->addFieldToFilter('created_at', ['gteq' => $dateLimit]);
        return $collection;
    }

    protected function processCollection($collection): void
    {
        if ($collection->getSize() <= 0) {
            return;
        }

        $this->logStart((int)$collection->getSize());
        $missingOrderGaDataIds = [];

        foreach ($collection as $gaData) {
            $this->processGaData($gaData, $missingOrderGaDataIds);
        }

        $this->logMissingOrderSummary($missingOrderGaDataIds);
    }

    protected function processGaData($gaData, array &$missingOrderGaDataIds): void
    {
        try {
            $order = $this->orderRepository->get($gaData->getOrderId());
            if (!$this->canSendOrder($order)) {
                return;
            }

            if (!$this->sendPurchase($order, $gaData)) {
                return;
            }

            $this->markAsSent($gaData);
            $this->logSuccess($order->getIncrementId());
        } catch (NoSuchEntityException $e) {
            $missingOrderGaDataIds[] = (string)$gaData->getId();
            $this->markAsOrphan($gaData);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Error processing GaData ID %s: %s', $gaData->getId(), $e->getMessage()));
        }
    }

    protected function canSendOrder($order): bool
    {
        return $order->getTotalInvoiced() > 0;
    }

    protected function sendPurchase($order, $gaData): bool
    {
        return $this->measurementProtocol->sendPurchase(
            $order,
            (string)$gaData->getClientId(),
            (string)$gaData->getSessionId()
        );
    }

    protected function markAsSent($gaData): void
    {
        $gaData->setStatus(GaData::STATUS_SENT);
        $gaData->setSentAt(date('Y-m-d H:i:s'));
        $this->gaDataResource->save($gaData);
    }

    protected function markAsOrphan($gaData): void
    {
        $gaData->setStatus(GaData::STATUS_ORPHAN);
        $this->gaDataResource->save($gaData);
    }

    protected function logStart(int $size): void
    {
        if (!$this->gtmHelper->canLogInfo()) {
            return;
        }

        $this->logger->info(sprintf('Starting GA4 Server-Side processing for %d orders', $size));
    }

    protected function logSuccess(string $incrementId): void
    {
        if (!$this->gtmHelper->canLogInfo()) {
            return;
        }

        $this->logger->info(sprintf('Purchase sent successfully for Order #%s', $incrementId));
    }

    /**
     * Purge old records to keep the table clean
     * 
     * @param string $dateLimit
     */
    protected function cleanup(string $dateLimit)
    {
        try {
            $connection = $this->gaDataResource->getConnection();
            $tableName = $this->gaDataResource->getMainTable();
            $connection->delete($tableName, ['created_at < ?' => $dateLimit]);
        } catch (\Exception $e) {
            $this->logger->error('Error cleaning up GA Data: ' . $e->getMessage());
        }
    }

    /**
     * @param array $missingOrderGaDataIds
     * @return void
     */
    protected function logMissingOrderSummary(array $missingOrderGaDataIds)
    {
        if (empty($missingOrderGaDataIds)) {
            return;
        }

        $this->logger->warning(
            sprintf(
                'GA4 fallback marked %d orphan ga_data record(s) in this run. IDs: %s',
                count($missingOrderGaDataIds),
                implode(',', $missingOrderGaDataIds)
            )
        );
    }
}
