<?php

namespace Swissup\Hreflang\Plugin\App\Request;

class StorePathInfoValidator extends AbstractPlugin
{
    /**
     * @param  \Magento\Store\App\Request\StorePathInfoValidator $subject
     * @param  string|null                                       $result
     * @param  \Magento\Framework\App\Request\Http               $request
     * @param  string                                            $pathInfo
     * @return string|null
     */
    public function afterGetValidStoreCode(
        \Magento\Store\App\Request\StorePathInfoValidator $subject,
        $result,
        \Magento\Framework\App\Request\Http $request,
        $pathInfo = ''
    ) {
        $currentStore = $this->helper->getCurrentStore();
        if ($currentStore && $this->helper->isLocaleInUrl($currentStore)) {
            return $currentStore->getCode();
        }

        return $result;
    }
}
