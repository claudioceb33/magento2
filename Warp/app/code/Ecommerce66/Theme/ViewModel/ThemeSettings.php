<?php

namespace Ecommerce66\Theme\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Ecommerce66\Theme\Helper\Data as HelperData;
use Magento\Store\Model\StoreManagerInterface;

class ThemeSettings implements ArgumentInterface
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * GuestOrders constructor.
     *
     * @param Helperdata            $helperData
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        HelperData $helperData,
        StoreManagerInterface $storeManager
    ) {
        $this->helperData = $helperData;
        $this->storeManager = $storeManager;
    }

    /**
     * @return bool
     */
    public function isNavigationSticky()
    {
        return $this->helperData->isNavigationSticky();
    }

    /**
     * @return string
     */
    public function getNavigationType()
    {
        return $this->helperData->getNavigationType();
    }

    /**
     * @return string
     */
    public function getNavigationLabel()
    {
        return $this->helperData->getNavigationLabel();
    }

    /**
     * @return array
     */
    public function getNavigationLinks()
    {
        return $this->helperData->getNavigationLinks();
    }

    /**
     * @param $product
     *
     * @return mixed|string
     */
    public function getShippingLabel($product)
    {
        $configs = $this->helperData->getShippingLabels();
        $price = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();

        return $this->getLabelByAmount($configs, $price);
    }

    /**
     * @param $product
     *
     * @return mixed|string
     */
    public function getPaymentLabel($product)
    {
        $configs = $this->helperData->getPaymentLabels();
        $price = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();

        return $this->getLabelByAmount($configs, $price);
    }

    /**
     * @param $configs
     * @param $price
     *
     * @return mixed|string
     */
    protected function getLabelByAmount($configs, $price)
    {
        $label = '';
        foreach ($configs as $config) {
            if ((int)$price >= (int)$config['amount']) {
                $label = $config['label'];
            }
        }

        return $label;
    }

    /**
     * @param $product
     *
     * @return array
     */
    public function getLabelsByAmount($product)
    {
        $labels = [];
        $shippingLabel = $this->getShippingLabel($product);
        if (!empty($shippingLabel)) {
            $labels['shipping'] = $shippingLabel;
        }
        $paymentLabel = $this->getPaymentLabel($product);
        if (!empty($paymentLabel)) {
            $labels['payment'] = $paymentLabel;
        }

        return $labels;
    }
}
