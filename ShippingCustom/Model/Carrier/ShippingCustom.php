<?php

namespace Ceb\ShippingCustom\Model\Carrier;

use Exception;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\Country;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Shipping\Model\Simplexml\ElementFactory;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Psr\Log\LoggerInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Framework\Xml\Security;
use Magecomp\Cityandregionmanager\Model\ResourceModel\Cities\CollectionFactory as Citiescollection;
use Magento\Checkout\Model\Session;
use Magento\Framework\Registry;
use Ceb\ShippingCustom\Helper\Data as Helper;

/**
 * Class ShippingCustom
 *
 * @version 1.0.0
 * @author Ceb <http://www.ceb.com> - Ecommerce done right
 * @copyright Copyright (c) 2021 Ceb
 * @package Ceb\ShippingCustom\Model\Carrier
 */
class ShippingCustom extends AbstractCarrierOnline implements CarrierInterface
{
    const CARRIER_CODE = 'shipping_custom';

    /**
     * @var string
     */
    protected $_code = self::CARRIER_CODE;

    /**
     * @var ResultFactory
     */
    protected $rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $rateMethodFactory;

    /**
     * @var ErrorFactory
     */
    protected $rateErrorFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Citiescollection
     */
    protected $citiesCollection;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * Hop constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param Security $xmlSecurity
     * @param ElementFactory $xmlElFactory
     * @param ResultFactory $rateFactory
     * @param MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
     * @param StatusFactory $trackStatusFactory
     * @param RegionFactory $regionFactory
     * @param CountryFactory $countryFactory
     * @param CurrencyFactory $currencyFactory
     * @param Data $directoryData
     * @param StockRegistryInterface $stockRegistry
     * @param RequestInterface $request
     * @param Citiescollection $citiesCollection
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        Security $xmlSecurity,
        ElementFactory $xmlElFactory,
        ResultFactory $rateFactory,
        MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        StatusFactory $trackStatusFactory,
        RegionFactory $regionFactory,
        CountryFactory $countryFactory,
        CurrencyFactory $currencyFactory,
        Data $directoryData,
        StockRegistryInterface $stockRegistry,
        RequestInterface $request,
        Citiescollection $citiesCollection,
        array $data = [],
        Registry $registry,
        Session $checkoutSession,
        Helper $helper
    )
    {
        $this->rateResultFactory = $rateFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->request           = $request;
        $this->citiesCollection  = $citiesCollection;
        $this->registry = $registry;
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;

        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );
    }
    /**
     * @return bool
     */
    public function isTrackingAvailable()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isCityRequired()
    {
        return false;
    }

    /**
     * @param null $countryId
     * @return bool
     */
    public function isZipCodeRequired($countryId = null)
    {
        return false;
    }

    /**
     * Is state province required
     *
     * @return bool
     */
    public function isStateProvinceRequired()
    {
        return false;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['shipping_custom' => $this->getConfigData('title')];
    }

    /**
     * @param RateRequest $request
     * @return bool|Result
     * @throws LocalizedException
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) return false;

        $quote = $this->checkoutSession->getQuote();
        $quote->setData("not_available_item", 0);

        $result = $this->rateResultFactory->create();
        $method = $this->rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('description'));

        $subTotalPrice = 0;
        $city = strtolower($request->getDestCity());
        $freeShippingCart = true;

        foreach($request->getAllItems() as $item)
        {
            $freeShippingItem = false;

            if($item->getProductType() == 'configurable')
                continue;

            $product = $item->getProduct();

            if($item->getParentItem())
                $item = $item->getParentItem();

            $freeCity = (boolean) $product->getResource()
                ->getAttributeRawValue($product->getId(), 'free_shipping_city', $product->getStoreId()) * $item->getQty();

            if ($freeCity) {
                $freeShippingItem = true;
            }

            if ($freeShippingItem == false) {
                $freeShippingCart = false;
            }

            $subTotalPrice += $product->getFinalPrice() * $item->getQty();
        }

        $destCity = $this->citiesCollection->create()
            ->addFieldToFilter('cities_name', $city)
            ->getFirstItem();

        if (!empty($destCity)) {
            $amounFreeShipping = (int) $destCity->getAmountFreeShipping();
            if ($amounFreeShipping > 0 && $subTotalPrice >= $amounFreeShipping && $freeShippingCart == false) {
                $quote->setData("not_available_item", 1);
                $quote->setData("msg_not_available_item", $this->helper->getMessageProductNotAvailableFreeShipping());
            }
            if ($amounFreeShipping > 0 && $subTotalPrice >= $amounFreeShipping && $freeShippingCart == true) {
                $method->setPrice(0);
                $method->setCost(0);
                $result->append($method);
            }
            if ($amounFreeShipping == 0 || $subTotalPrice < $amounFreeShipping) {
                $quote->setData("not_available_item", 0);
            }
        }
        
        return $result;
    }

    /**
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
     *
     * @param DataObject $request
     * @return DataObject
     * @throws Exception
     */
    protected function _doShipmentRequest(DataObject $request)
    {
        $this->_prepareShipmentRequest($request);
    }
}