<?php

namespace Swissup\SeoUrls\Block\System\Config\Ajaxlayerednavigation;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\ScopeInterface;
use Swissup\SeoUrls\Helper\Data as Helper;

class IsCategoryMultiple extends Field
{
    protected function _getElementHtml(AbstractElement $element)
    {
        if ($this->isForceSubcategory()) {
            $url = $this->getUrl('*/*/*', [
                '_current' => true,
                'section' => 'swissup_seourls',
                'group' => 'layered_navigation',
                'field' => 'category_child_url'
            ]);
            $text = __('Use direct URL to subcategory');
            $linkHtml = "<a href=\"{$url}\">{$text}</a>";

            return __(
                '<div class="admin__control-text" style="background-color: rgba(233,233,233,.6)"><small>' .
                    '<b style="color: red">This setting is disabled</b>' .
                    '<br>' .
                    'Because %1 is enabled in SEO Urls (Swissup SEO Suite).' .
                '</small></div>',
                $linkHtml
            );
        }

        return parent::_getElementHtml($element);

    }

    protected function _isInheritCheckboxRequired(AbstractElement $element)
    {
        return !$this->isForceSubcategory();
    }

    private function isForceSubcategory(): bool
    {
        return $this->isSetConfigFlag(Helper::CONFIG_ENABLED)
            && $this->isSetConfigFlag(Helper::CONFIG_FORCE_SUBCAT_URL);
    }

    private function isSetConfigFlag($path): bool
    {
        $config = $this->_scopeConfig;
        $store = $this->getRequest()->getParam('store', false);
        if ($store !== false) {
            return $config->isSetFlag($path, ScopeInterface::SCOPE_STORE, $store);
        }

        $website = $this->getRequest()->getParam('website', false);
        if ($website !== false) {
            return $config->isSetFlag($path, ScopeInterface::SCOPE_WEBSITE, $website);
        }

        return $config->isSetFlag($path);
    }

}
