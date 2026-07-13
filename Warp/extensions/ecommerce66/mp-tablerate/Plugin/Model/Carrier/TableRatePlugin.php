<?php
namespace Ecommerce66\TableRateShipping\Plugin\Model\Carrier;

use Ecommerce66\TableRateShipping\Helper\Data;

use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item;
use Mageplaza\TableRateShipping\Model\Carrier\TableRate;
use Mageplaza\TableRateShipping\Model\Source\VolumeWeight;

class TableRatePlugin
{
    /**
     * @var Data
     */
    protected $helper;

    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    public function aroundGetCartData(
        TableRate $subject,
        \Closure $proceed,
        $request,
        $rate = null
    ) {

        if ($this->helper->getIsEnabled()) {
            $cartData = [
                'weight'   => 0,
                'subtotal' => 0,
                'qty'      => 0,
            ];

            $shipTypes = $rate && $rate->getShippingGroup() ? explode(',', $rate->getShippingGroup()) : null;

            /** @var Item $item */
            foreach ($request->getAllItems() as $item) {
                if ($item->getIsVirtual() && !$this->helper->getConfigData('include_virtual_price')) {
                    continue;
                }

                if ($rate) {
                    if ($item->getFreeShipping()) {
                        continue;
                    }

                    /** validate shipping group of non-composite product */
                    if ($shipTypes && !in_array($item->getProductType(), ['bundle', 'configurable'], true)) {
                        $type = $this->helper->getProdAttrVal($item->getProduct(), Data::SHIP_TYPE_ATTR);
                        if ($type && !in_array($type, $shipTypes, true)) {
                            continue;
                        }
                    }
                }

                /**
                 * get weight from all product type except configurable
                 * include simple, grouped, bundle, children of configurable and bundle
                 */
                if ($item->getProductType() !== 'configurable') {
                    $qty = $item->getQty();

                    if ($parent = $item->getParentItem()) {
                        $qty *= $parent->getQty();
                    }

                    $cartData['weight'] += $this->getItemWeight($item) * $qty;
                }

                /** get visible item */
                if (!$item->getParentItem()) {
                    $itemPrice = $this->helper->getCatalogPriceInclTax() ? $item->getPriceInclTax(): $item->getPrice();
                    $cartData['subtotal'] += ($itemPrice -($item->getDiscountAmount()/$item->getQty())) * $item->getQty();
                    $cartData['qty']      += $item->getQty();
                }
            }
            return $cartData;
        }
        return $proceed($request, $rate);
    }

    /**
     * @param Item $item
     *
     * @return float
     */
    public function getItemWeight($item)
    {
        if (!$product = $this->determineProduct($item)) {
            return 0;
        }

        $weight = 0;

        switch ($this->helper->getConfigData('volume_weight')) {
            case VolumeWeight::WEIGHT:
                $weight = $this->helper->getProdAttrVal($product, $this->helper->getConfigData('weight_attribute'));

                if (!is_numeric($weight)) {
                    $weight = 0;
                }

                break;
            case VolumeWeight::V_ATTRIBUTE:
                $vol = $this->helper->getProdAttrVal($product, $this->helper->getConfigData('v_attribute'));

                if (!is_string($vol)) {
                    break;
                }

                foreach (explode('x', strtolower($vol)) as $value) {
                    if (is_numeric($value)) {
                        $weight = $weight ? $weight * $value : $value;
                    }
                }

                $weight /= $this->helper->getConfigData('shipping_factor') ?: 1;

                break;
            case VolumeWeight::USER_ATTRIBUTE:
                for ($i = 1; $i <= 3; $i++) {
                    $value = $this->helper->getProdAttrVal($product, $this->helper->getConfigData('user_attribute_' . $i));

                    if (is_numeric($value)) {
                        $weight = $weight ? $weight * $value : $value;
                    }
                }

                $weight /= $this->helper->getConfigData('shipping_factor') ?: 1;

                break;
        }

        $orgWeight = $this->helper->getProdAttrVal($product, 'weight');

        if (is_numeric($orgWeight) && $orgWeight > $weight) {
            return $orgWeight;
        }

        return (float) $weight;
    }

    /**
     * @param Item $item
     *
     * @return Product|null
     */
    private function determineProduct($item)
    {
        if ($item->getParentItemId() && $item->getParentItem()->getProductType() === 'bundle') {
            $isDynamic = $this->helper->getProdAttrVal($item->getParentItem()->getProduct(), 'weight_type');

            return $isDynamic ? null : $item->getProduct();
        }

        if ($item->getProductType() === 'bundle') {
            $isDynamic = $this->helper->getProdAttrVal($item->getProduct(), 'weight_type');

            return $isDynamic ? $item->getProduct() : null;
        }

        return $item->getProduct();
    }
}
