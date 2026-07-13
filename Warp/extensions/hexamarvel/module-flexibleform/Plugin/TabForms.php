<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Plugin;

class TabForms
{
    /**
     * @var \Hexamarvel\FlexibleForm\Helper\Data
     */
    private $tabHelper;

    /**
     *
     * @param \Hexamarvel\FlexibleForm\Helper\Data $tabHelper
     */
    public function __construct(
        \Hexamarvel\FlexibleForm\Helper\Data $tabHelper
    ) {
        $this->tabHelper = $tabHelper;
    }

    /**
     * @param \Magento\Catalog\Block\Product\View\Details $subject
     * @param array $result
     * @return array
     */
    public function afterGetGroupSortedChildNames(
        \Magento\Catalog\Block\Product\View\Details $subject,
        $result
    ) {
        if (!$this->tabHelper->isEnabled() ||
            empty($this->tabHelper->getConfig('hexaform/product_inquiry_settings/tab_title')) ||
            !$this->tabHelper->getConfig('hexaform/product_inquiry_settings/enable_enquiry')
        ) {
            return $result;
        }
        array_push($result, 'product.info.details.forminquiry');

        return $result;
    }
}
