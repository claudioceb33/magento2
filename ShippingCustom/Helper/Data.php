<?php

namespace Ceb\ShippingCustom\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Data
 *
 * @version 1.0.0
 * @author Ceb <http://www.ceb.com> - Ecommerce done right
 * @copyright Copyright (c) 2024 Ceb
 * @package Ceb\Hop\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManagerInterface;

    /**
     * Data constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManagerInterface
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManagerInterface
    ) {
        $this->scopeConfig             = $scopeConfig;
        $this->storeManagerInterface   = $storeManagerInterface;
    }

    /**
     * @return string
     */
    public function isEnable()
    {
        return (bool) $this->scopeConfig->getValue('carriers/shipping_custom/active', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $path
     * @param $params
     * @return string
     * @throws NoSuchEntityException
     */
    public function getStoreUrl($path,$params)
    {
        return $this->storeManagerInterface->getStore()->getUrl($path,$params);
    }

    /**
     * @return float
     */
    public function getMaxWeight()
    {
        return (float)$this->scopeConfig->getValue("carriers/shipping_custom/max_package_weight", ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getMessageProductNotAvailableFreeShipping()
    {
        return $this->scopeConfig->getValue("carriers/shipping_custom/message_product_not_available", ScopeInterface::SCOPE_STORE);
    }
}

