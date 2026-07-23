<?php

namespace Ceb\OrderCancel\Cron;

use Ceb\OrderCancel\Helper\Data;
use Magento\Framework\App\ResourceConnection;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use MercadoPago\AdbPayment\Gateway\Config\ConfigCheckoutPro;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Framework\DB\Transaction;

/**
 * CronTab for cancel Checkout Pro.
 */
class UpdateOrders
{
    /**
     * @var ConfigCheckoutPro
     */
    protected $configCheckoutPro;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var Data
     */
    protected $data;

    /**
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * @var InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * Constructor.
     *
     * @param ConfigCheckoutPro $configCheckoutPro
     * @param CollectionFactory $collectionFactory
     * @param ResourceConnection $resource
     * @param Data $data
     * @param InvoiceService $invoiceService;
     * @param InvoiceSender $invoiceSender
     * @param Transaction $transaction
     */
    public function __construct(
        ConfigCheckoutPro $configCheckoutPro,
        CollectionFactory $collectionFactory,
        ResourceConnection $resource,
        Data $data,
        InvoiceService $invoiceService,
        InvoiceSender $invoiceSender,
        Transaction $transaction
    ) {
        $this->configCheckoutPro = $configCheckoutPro;
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->data = $data;
        $this->invoiceService = $invoiceService;
        $this->invoiceSender = $invoiceSender;
        $this->transaction = $transaction;
    }

    /**
     * Get sales_order_payment table name.
     *
     * @return string
     */
    public function getSalesOrderPaymentTableName()
    {
        return $this->resource->getTableName('sales_order_payment');
    }

    /**
     * Execute the cron.
     *
     * @return void
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.MissingImport)
     */
    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/cron_update_orders.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $hoursFrom = $this->data->getConfigHoursFrom();
        $hoursFrom *= 60;
        $daysTo = $this->data->getConfigDaysTo();
        $paymentsConfig = $this->data->getPaymentsSelects();

        $ordersToCancel = $this->collectionFactory->create()
            ->addFieldToFilter('state', Order::STATE_NEW);

        $soapTable = $this->getSalesOrderPaymentTableName();
        $ordersToCancel->getSelect()
            ->join(
                ['sop' => $soapTable],
                'main_table.entity_id = sop.parent_id',
                ['method']
            )
            ->where('sop.method IN (?)', $paymentsConfig);

        $ordersToCancel->getSelect()
            ->where("TIMESTAMPDIFF(MINUTE, main_table.created_at, NOW()) >= $hoursFrom AND DATEDIFF(NOW(),main_table.created_at) <= $daysTo");

        foreach ($ordersToCancel as $order) {
            $storeId = $order->getStoreId();
            $incrementId = $order->getIncrementId();
            $rejected = true;

            if ($order->getPayment()->getMethod() == ConfigCheckoutPro::METHOD) {
                $rejected = false;
                $results = $this->data->getMpPaymentStatus($storeId, $incrementId);
                if (isset($results['response']['results'][0]['status']) && $results['response']['results'][0]['status'] == 'rejected') {
                    $rejected = true;
                } elseif (isset($results['response']['paging']['total']) && $results['response']['paging']['total'] == 0) {
                    $rejected = true;
                }
            } elseif ($this->isMobbexOrder($order)) {
                $statusCode = $this->getMobbexStatusCode($order);
                $rejected = $statusCode === 604;
            }

            try {
                if ($rejected) {
                    $order->setStatus(Order::STATE_CANCELED);
                    $order->setState(Order::STATE_CANCELED);
                    $order->save();
                    $logger->info('Cancel Order ' . $order->getIncrementId() . ' successfully');
                }
            } catch (\Exception $e) {
                $logger->info('Cancel order execution error: ' . $e->getMessage());
            }
        }

        $ordersToProcessing = $this->collectionFactory->create()
            ->addFieldToFilter('state', Order::STATE_NEW);

        $soapTable = $this->getSalesOrderPaymentTableName();
        $ordersToProcessing->getSelect()
            ->join(
                ['sop' => $soapTable],
                'main_table.entity_id = sop.parent_id',
                ['method']
            )
            ->where('sop.method = ?', ConfigCheckoutPro::METHOD);

        $ordersToProcessing->getSelect()
            ->where("TIMESTAMPDIFF(MINUTE, main_table.created_at, NOW()) >= $hoursFrom AND DATEDIFF(NOW(),main_table.created_at) <= $daysTo");

        try {
            foreach ($ordersToProcessing as $order) {
                $storeId = $order->getStoreId();
                $incrementId = $order->getIncrementId();

                $results = $this->data->getMpPaymentStatus($storeId, $incrementId);

                if (isset($results['response']['results'][0]['status']) && $results['response']['results'][0]['status'] == 'approved') {
                    $this->moveOrderToProcessing($order);
                }

                $logger->info('Processing Order ' . $order->getIncrementId() . ' successfully');
            }
        } catch (\Exception $e) {
            $logger->info('Processing order execution error: ' . $e->getMessage());
        }

        $mobbexOrders = $this->collectionFactory->create()
            ->addFieldToFilter('status', 'pending');

        $soapTable = $this->getSalesOrderPaymentTableName();
        $mobbexOrders->getSelect()
            ->join(
                ['sop' => $soapTable],
                'main_table.entity_id = sop.parent_id',
                ['method']
            )
            ->where('sop.method = ?', 'sugapay');

        $mobbexOrders->getSelect()
            ->where("TIMESTAMPDIFF(MINUTE, main_table.created_at, NOW()) >= $hoursFrom AND DATEDIFF(NOW(),main_table.created_at) <= $daysTo");
        try {
            foreach ($mobbexOrders as $order) {
                $statusCode = $this->getMobbexStatusCode($order);

                if ($statusCode !== null && $this->isMobbexApprovedStatus($statusCode)) {
                    $this->moveOrderToProcessing($order);
                } elseif ($statusCode === 604) {
                    $order->setStatus(Order::STATE_CANCELED);
                    $order->setState(Order::STATE_CANCELED);
                    $order->save();
                }

                $logger->info('Mobbex sync for Order ' . $order->getIncrementId() . ' successfully');
            }
        } catch (\Exception $e) {
            $logger->info('Mobbex sync execution error: ' . $e->getMessage());
        }
    }

    /**
     * @param Order $order
     * @return bool
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function isMobbexOrder(Order $order)
    {
        return $order->getPayment() && $order->getPayment()->getMethod() === 'sugapay';
    }

    /**
     * @param Order $order
     * @return int|null
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function getMobbexStatusCode(Order $order)
    {
        if (!class_exists(\Mobbex\Modules\Checkout::class) || !class_exists(\Mobbex\Repository::class)) {
            return null;
        }

        $reference = \Mobbex\Modules\Checkout::generateReference($order->getIncrementId());
        $operation = \Mobbex\Repository::getOperationFromReference($reference);

        if (isset($operation['status']['code'])) {
            return (int) $operation['status']['code'];
        }

        if (isset($operation['status_code'])) {
            return (int) $operation['status_code'];
        }

        if (isset($operation['status'])) {
            return (int) $operation['status'];
        }

        return null;
    }

    /**
     * @param int $statusCode
     * @return bool
     */
    protected function isMobbexApprovedStatus($statusCode)
    {
        return $statusCode === 4 || ($statusCode >= 200 && $statusCode < 400);
    }

    /**
     * @param Order $order
     * @return void
     * @throws \Exception
     */
    protected function moveOrderToProcessing(Order $order)
    {
        if ($order->canInvoice()) {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
            $invoice->register();
            $invoice->save();
            $this->invoiceSender->send($invoice);

            $transactionSave = $this->transaction->addObject($invoice)
                ->addObject($invoice->getOrder());
            $transactionSave->save();
        }

        $order->setStatus(Order::STATE_PROCESSING);
        $order->setState(Order::STATE_PROCESSING);
        $order->save();
    }
}
