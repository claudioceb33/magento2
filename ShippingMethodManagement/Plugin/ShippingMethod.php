<?php 
namespace Ceb\ShippingMethodManagement\Plugin;

class ShippingMethod
{    
    public $code = [];
    
    public function beforeAppend($subject, $result)
    { 
        if (!$result instanceof \Magento\Quote\Model\Quote\Address\RateResult\Method) {    
            return [$result]; 
        } 

        $this->getShipCode($result);


        if ($this->isMethodRestricted($result)) { 
            try{
                $result->setIsDisabled(true);
            } catch(Exception $e) {
                $result->setIsDisabled(true);
            }
        }

        return [$result]; 
    }
    
    public function getShipCode($shippingModel)
    {
        $this->code[] = $shippingModel->getCarrier(); 
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function isMethodRestricted($shippingModel)
    {
        $carrier = $shippingModel->getCarrier();
        $code = $shippingModel->getMethod();

        if(in_array($carrier, $this->code) && $carrier == 'carrier') {
            return false;
        }

        if(in_array($code, $this->code) && $code == 'method') {
            return false;
        }

        

        return true; 
    }
}