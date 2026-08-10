<?php
namespace Ceb\ShippingMethodManagement\Plugin\Model;
 
class ShippingMethodManagement
{
    public function afterEstimateByAddress($shippingMethodManagement, $output)
    {
        return $this->filterOutput($output);
    }

    public function afterEstimateByExtendedAddres($shippingMethodManagement, $output)
    {
        return $this->filterOutput($output);
    }
 
    public function afterEstimateByAddressId($shippingMethodManagement, $output)
    {
        return $this->filterOutput($output);
    }

    private function filterOutput($output)
    {
        $outputCustom = [];
        foreach ($output as $shippingMethod) {
            if ($shippingMethod->getCarrierCode() == 'custom_shipping') {
                $outputCustom[] = $shippingMethod;
            }
        }
        if (!empty($outputCustom)) {
            return $outputCustom;
        }
        return $output;
    }
}