<?php

namespace Swissup\SeoUrls\Plugin\Ajaxlayerednavigation\ViewModel;

class ConfigPlugin
{
    /**
     * @var \Swissup\SeoUrls\Helper\Data
     */
    private $helper;

    /**
     * @param \Swissup\SeoUrls\Helper\Data $helper
     */
    public function __construct(
        \Swissup\SeoUrls\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Swissup\Ajaxlayerednavigation\ViewModel\Config $subject
     * @param $result
     * @return false|bool
     */
    public function afterIsCategoryMultipleEnabled(
        \Swissup\Ajaxlayerednavigation\ViewModel\Config $subject,
        $result
    ) {
        $isSeoUrlsEnabled = $this->helper->isSeoUrlsEnabled();
        $isForceSubcategoryUrl = $this->helper->isForceSubcategoryUrl();

        if ($isSeoUrlsEnabled && $isForceSubcategoryUrl) {
            return false;
        }

        return $result;
    }
}
