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

namespace Lof\Formbuilder\Block\Widget;

use Lof\Formbuilder\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\Url;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;

class Form extends \Lof\Formbuilder\Block\Form implements BlockInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Store manager
     *
     * @var Repository
     */
    public $assetRepository;

    /**
     * Form constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Data $helper
     * @param \Lof\Formbuilder\Model\Form $form
     * @param Url $url
     * @param RequestInterface $request
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $helper,
        \Lof\Formbuilder\Model\Form $form,
        Url $url,
        RequestInterface $request,
        Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $registry, $helper, $form, $url, $request, $customerSession, $data);
        $this->helper = $helper;
        $this->assetRepository = $context->getAssetRepository();
        $my_template = "widget/form.phtml";
        if ($this->hasData("block_template") && $this->getData("block_template")) {
            $my_template = $this->getData("block_template");
        }

        $this->setTemplate($my_template);
    }

    /**
     * @inheritdoc
     */
    public function _toHtml()
    {
        $store = $this->_storeManager->getStore();
        $form = '';
        if ($formId = $this->getData('formid')) {
            $form = $this->form->setStore($store)->load((int)$formId);
        } elseif ($formIdentifier = $this->getData('identifier')) {
            $form = $this->form->setStore($store)->loadByAlias($formIdentifier);
        }
        if ($form) {
            $customerGroups = $form->getData('customergroups');
            $customerGroupId = $this->customerSession->getCustomerGroupId();
            $stores = $form->getStores();
            $customerGroups = !empty($customerGroups) ? $customerGroups : [0];
            $stores = !empty($stores) ? $stores : [0];

            if (!in_array(0, $customerGroups) && !$this->customerSession->isLoggedIn()) {
                return null;
            }
            if (!in_array(0, $stores) && !in_array($store->getId(), $stores)) {
                return null;
            }
            if (!in_array($customerGroupId, $customerGroups)) {
                return null;
            }
            if (!$form->getStatus()) {
                return null;
            }
            $this->setCurrentForm($form);
        }
        return parent::_toHtml();
    }
}
