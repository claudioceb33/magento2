<?php

namespace Swissup\SeoPager\Helper;

use Magento\Catalog\Model\Product\ProductList\Toolbar as ToolbarModel;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Get URL to "view all" page
     *
     * @param  boolean $addCurrentRequestParams
     * @return string
     */
    public function getViewAllPageUrl($addCurrentRequestParams = true)
    {
        $urlParams = [
            '_current' => $addCurrentRequestParams,
            '_escape' => true,
            '_use_rewrite' => true,
            '_query' => [
                    '_' => null, // remove mystic underscore param
                    ToolbarModel::PAGE_PARM_NAME => null, // remove page param
                    ToolbarModel::LIMIT_PARAM_NAME => 'all'
                ]
        ];
        return $this->_getUrl('*/*/*', $urlParams);
    }

    public function getPageUrl(
        int $pageNumber,
        bool $addCurrentRequestParams = true
    ): string {
        $urlParams = [
            '_current' => $addCurrentRequestParams,
            '_escape' => true,
            '_use_rewrite' => true,
            '_query' => [
                    '_' => null, // remove mystic underscore param
                    ToolbarModel::PAGE_PARM_NAME => $pageNumber === 1 ? null : $pageNumber
                ]
        ];

        return $this->_getUrl('*/*/*', $urlParams);
    }

    /**
     * Is view all button for pagination available
     *
     * @return boolean
     */
    public function canShowViewAllLink()
    {
        return $this->scopeConfig->isSetFlag(
            'catalog/frontend/list_allow_all',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get strategy of paginated content presentation for search engines
     *
     * @return string
     */
    public function getPresentationStrategy()
    {
        if ($this->canShowViewAllLink()) {
            return $this->scopeConfig->getValue(
                'swissup_seopager/general/strategy',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }

        return $this->scopeConfig->getValue(
            'swissup_seopager/general/strategy_no_view_all',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if SEO Pager module enabled.
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'swissup_seopager/general/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get template for page title tail
     *
     * @return string
     */
    public function getTitleTemplate()
    {
        return $this->scopeConfig->getValue(
            'swissup_seopager/page_title/template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
