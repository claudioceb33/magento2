<?php

namespace Ecommerce66\TableRateShipping\Helper;

use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\File\Size;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\TableRateShipping\Helper\Data as DataHelper;

/**
 * Class Data
 * @package Ecommerce66\TableRateShipping\Helper
 */
class Data extends DataHelper
{
    protected const TAX_CONFIG_PATH     = 'tax/calculation/price_includes_tax';
    protected const ENABLED_PATH        = 'e66tablerate/settings/enable';
    public const CONFIG_MODULE_PATH     = 'carriers/mptablerate/';
    public const SHIP_TYPE_ATTR         = 'mptablerate_shipping_group';
    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param Repository $repository
     * @param Size $fileSize
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        Repository $repository,
        Size $fileSize
    ) {

        parent::__construct(
            $context,
            $objectManager,
            $storeManager,
            $repository,
            $fileSize
        );
    }

    /**
     * @param string $postcode
     *
     * @return array
     */
    public function getPostcodeData($postcode)
    {
        $result = ['alpha' => '', 'num' => ''];
        $postcode = $postcode ?? '';
        foreach (str_split($postcode) as $item) {
            if ($item === ' ') {
                continue;
            }

            if (is_numeric($item)) {
                $result['num'] .= $item;
            } else {
                $result['alpha'] .= $item;
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function getCatalogPriceInclTax(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::TAX_CONFIG_PATH);
    }


    /**
     * @return bool
     */
    public function getIsEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::ENABLED_PATH);
    }

    public function getConfigData($field) {
        return $this->scopeConfig->getValue(self::CONFIG_MODULE_PATH.$field);
    }
}
