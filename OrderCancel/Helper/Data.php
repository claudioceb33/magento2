<?php
declare(strict_types=1);

namespace Ceb\OrderCancel\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
/*
use MercadoPago\PP\Sdk\HttpClient\HttpClient;
use MercadoPago\PP\Sdk\HttpClient\Requester\CurlRequester;
use MercadoPago\AdbPayment\Gateway\Config\Config;
*/
use Magento\Payment\Model\Method\Logger;

class Data
{
    const MP_ENDPOINT_API = 'https://api.mercadopago.com';
    const CONFIG_PAYMENTS = 'checkout/options/payments';
    const CONFIG_HOURS = 'checkout/options/cancel_order_from';
    const CONFIG_DAYS = 'checkout/options/cancel_order_to';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /*
    /**
     * @var Config
     */
    /*protected $config;*/

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Data constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        /*Config $config,*/
        Logger $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        /*$this->config = $config;*/
        $this->logger = $logger;
    }

    public function getConfigHoursFrom() {
        $result = $this->scopeConfig->getValue(self::CONFIG_HOURS, ScopeInterface::SCOPE_STORE);
        if (empty($result)) $result = 3;
        return $result;
    }

    public function getConfigDaysTo() {
        $result = $this->scopeConfig->getValue(self::CONFIG_DAYS, ScopeInterface::SCOPE_STORE);
        if (empty($result)) $result = 30;
        return $result;
    }

    public function getPaymentsSelects()
    {
        $payments = $this->scopeConfig->getValue(self::CONFIG_PAYMENTS, ScopeInterface::SCOPE_STORE);
        if (empty($payments)) return [];
        $payments = json_decode($payments);
        $paymentsArr = [];
        foreach ($payments as $payment) {
            if (isset($payment->payment_method)) {
                $paymentsArr[] = $payment->payment_method;
            }
        }
        return $paymentsArr;
    }

    /**
     * Gets the API endpoint URL.
     *
     * @return string
     */
    public function getMpApiUrl() {
        return self::MP_ENDPOINT_API;
    }

    public function getMpPaymentStatus(?int $storeId = null, $incrementId = null) {

        return [
            'success'    => true,
            'response'   => 'Success'
        ];

        /*No se ejecuta la consulta a MP*/

        /*
        $requester = new CurlRequester();
        $baseUrl = $this->getMpApiUrl();
        $client  = new HttpClient($baseUrl, $requester);
        $daysTo = (int) $this->getConfigDaysTo();
        $daysTo++;

        $uri = '/v1/payments/search?range=date_created&begin_date=NOW-'.$daysTo.'DAYS&end_date=NOW&external_reference='.$incrementId;
        $clientHeaders = $this->config->getClientHeadersMpPluginsPhpSdk($storeId);

        try {
            $result = $client->get($uri, $clientHeaders);
            $response = $result->getData();

            if($result->getStatus() > 299) {
                $this->logger->debug(
                    [
                        'url'       => $baseUrl . $uri,
                        'status'    => $result->getStatus(),
                        'response'  => $response
                    ]
                );
            }
            return [
                'success'    => isset($response['message']) ? false : true,
                'response'   => $response,
            ];
        } catch (\Exception $e) {
            $this->logger->debug(
                [
                    'url'       => $baseUrl . $uri,
                    'error'     => $e->getMessage(),
                ]
            );
            return ['success' => false, 'error' =>  $e->getMessage()];
        }
        */
    }

}
