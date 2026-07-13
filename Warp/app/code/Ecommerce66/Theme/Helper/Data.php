<?php
declare(strict_types=1);

namespace Ecommerce66\Theme\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    protected const NATIVE_NAV   = 'default';
    protected const CONFIG_PATH  = 'theme66/settings/';
    protected const NAV_STICKY   = 'nav_sticky';
    protected const NAV_TYPE     = 'nav_type';
    protected const NAV_LABEL    = 'nav_vertical_label';
    protected const NAV_LINKS    = 'nav_vertical_links';
    protected const SHIPPING_LBL = 'sipping_labels';
    protected const PAYMENT_LBL  = 'payment_labels';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var array
     */
    protected $shippingLabels;

    /**
     * @var array
     */
    protected $paymentLabels;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Data constructor.
     *
     * @param Context               $context
     * @param ScopeConfigInterface  $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Json                  $json
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Json $json
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->json = $json;
        parent::__construct($context);
    }

    /**
     * @param $field
     *
     * @return mixed
     */
    protected function getConfig($field)
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->scopeConfig->getValue(self::CONFIG_PATH . $field, 'store', $storeId);
    }

    /**
     * @return bool
     */
    public function isNavigationSticky()
    {
        return (bool)(int)$this->getconfig(self::NAV_STICKY);
    }

    /**
     * @return string
     */
    public function getNavigationType()
    {
        $navType = (string)$this->getconfig(self::NAV_TYPE);
        return !empty($navType) ? $navType : self::NATIVE_NAV;
    }

    /**
     * @return string
     */
    public function getNavigationLabel()
    {
        return (string)$this->getconfig(self::NAV_LABEL);
    }

    /**
     * @return array
     */
    public function getNavigationLinks()
    {
        $navLinks = $this->getconfig(self::NAV_LINKS);
        return empty($navLinks) ? [] : $this->json->unserialize($navLinks);
    }

    /**
     * @return string
     */
    public function getShippingLabels()
    {
        if (!is_array($this->shippingLabels)) {
            $json = $this->getconfig(self::SHIPPING_LBL);
            $this->shippingLabels = empty($json) ? [] : $this->json->unserialize($json);
        }
        return $this->shippingLabels;
    }

    /**
     * @return string
     */
    public function getPaymentLabels()
    {
        if (!is_array($this->paymentLabels)) {
            $json = $this->getconfig(self::PAYMENT_LBL);
            $this->paymentLabels = empty($json) ? [] : $this->json->unserialize($json);
        }
        return $this->paymentLabels;
    }

}
