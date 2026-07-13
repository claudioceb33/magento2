<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_Formbuilder
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\Formbuilder\Block;

use Lof\Formbuilder\Helper\Data;
use Lof\Formbuilder\Model\Modelcategory;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Toplinks extends Template
{
    /**
     * @var Modelcategory
     */
    private $formCategory;

    /**
     * @var \Lof\Formbuilder\Model\Form
     */
    private $form;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * Toplinks constructor.
     * @param Context $context
     * @param Modelcategory $modelCategory
     * @param \Lof\Formbuilder\Model\Form $form
     * @param Data $helper
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        Modelcategory $modelCategory,
        \Lof\Formbuilder\Model\Form $form,
        Data $helper,
        Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->formCategory = $modelCategory;
        $this->form = $form;
        $this->helper = $helper;
        $this->customerSession = $customerSession;
    }

    /**
     * Render block HTML
     *
     * @inheritdoc
     */
    protected function _toHtml()
    {
        $store = $this->_storeManager->getStore();
        if (!$this->helper->getConfig('general_settings/enable')) {
            return '';
        }
        $collection = $this->form->getCollection();
        $collection->addFieldToFilter("status", 1)->addFieldToFilter("show_toplink", 1);
        $link = '';
        $route = $this->helper->getConfig('general_settings/route');

        if ($route != '') {
            $route = $route . '/';
        }
        if ($collection->getSize()) {
            $customerGroupid = $this->customerSession->getCustomerGroupId();
            foreach ($collection as $item) {
                $groups = $item->getData('customergroups');
                $groups = is_array($groups) ? $groups : explode(",", $groups);
                $stores = $item->getStores();
                $stores = is_array($stores) ? $stores : explode(",", $stores);
                //$formId = $item['form_id'];
                if (
                    in_array($customerGroupid, $groups)
                    && (in_array(0, $stores) || in_array(
                        $store->getId(),
                        $stores
                    ))
                ) {
                    $link .= '<li><a href="' . $this->getUrl($route .
                            $item->getData('identifier')) . '"> ' . $this->escapeHtml($item->getTitle()) . ' </a></li>';
                }
            }
        }
        return $link;
    }

    /**
     * @return $this
     */
    public function addCustomFormLinks()
    {
        $parentBlock = $this->getParentBlock();
        if ($parentBlock) {
            //get Form Collection
            $collection = $this->form->getCollection();
            $collection->addFieldToFilter("status", 1)
                ->addFieldToFilter("show_toplink", 1);
            $link = '';
            if ($collection->getSize()) {
                foreach ($collection as $item) {
                    $link .= '<a href="' . $item->getFormLink() .
                        '"> ' . $this->escapeHtml($item->getTitle()) . ' </a>';
                }
            }
        }
        return $this;
    }
}
