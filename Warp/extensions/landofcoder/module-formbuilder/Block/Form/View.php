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

namespace Lof\Formbuilder\Block\Form;

use Lof\Formbuilder\Helper\Data;
use Lof\Formbuilder\Model\Form;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Url;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\Template\Context;

class View extends \Lof\Formbuilder\Block\Form
{
    /**
     *
     * @var Registry|null
     */
    protected $coreRegistry = null;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var
     */
    protected $collection;

    /**
     * Store manager
     *
     * @var Repository
     */
    public $assetRepository;

    /**
     * View constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Data $helper
     * @param Form $form
     * @param Url $url
     * @param RequestInterface $request
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $helper,
        Form $form,
        Url $url,
        RequestInterface $request,
        Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $registry, $helper, $form, $url, $request, $customerSession, $data);
        $this->helper = $helper;
        $this->assetRepository = $context->getAssetRepository();
    }

    /**
     * @inheritdoc
     */
    protected function _beforeToHtml()
    {
        $form = $this->getCurrentForm();
        $customTemplate = $form->getData('custom_template');
        $customTemplate = $customTemplate ? @trim($customTemplate) : "";
        if ($customTemplate) {
            $this->setTemplate($customTemplate);
        }
        return parent::_beforeToHtml();
    }

    /**
     * @inheritdoc
     */
    protected function _addBreadcrumbs()
    {
        $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $form = $this->getCurrentForm();
        $pageTitle = $form->getPageTitle();
        if ($pageTitle == '') {
            $pageTitle = $form->getTitle();
        }
        //$route = $this->helper->getConfig('general_settings/route');
        if ($breadcrumbsBlock) {
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $baseUrl
                ]
            );
            $breadcrumbsBlock->addCrumb(
                'lofformbuilder',
                [
                    'label' => @trim($pageTitle),
                    'title' => @trim($pageTitle),
                    'link' => ''
                ]
            );
        }
    }

    /**
     * @param $collection
     * @return mixed
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
        return $this->collection;
    }

    /**
     * @return mixed
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        $form = '';
        try {
            $form = $this->getCurrentForm();
        } catch (NoSuchEntityException | LocalizedException $e) {
        }
        if ($form) {
            $pageTitle = $form->getPageTitle();
            if ($pageTitle == '') {
                $pageTitle = $form->getTitle();
            }
            $metaDescription = $form->getMetaDescription();
            $metaKeywords = $form->getMetaKeywords();

            try {
                $this->_addBreadcrumbs();
            } catch (NoSuchEntityException | LocalizedException $e) {
            }
            $this->pageConfig->addBodyClass('formbuilder-form-' . $form->getIdentifier());
            if ($pageTitle) {
                $this->pageConfig->getTitle()->set($pageTitle);
            }
            if ($metaKeywords) {
                $this->pageConfig->setKeywords($metaKeywords);
            }
            if ($metaDescription) {
                $this->pageConfig->setDescription($metaDescription);
            }
        }
        return parent::_prepareLayout();
    }

    /**
     * @inheritdoc
     */
    public function _toHtml()
    {
        $store = $this->_storeManager->getStore();
        $form = '';
        $form = $this->getCurrentForm();
        if ($form) {
            $customerGroups = $form->getData('customergroups');
            $customerGroupId = $this->customerSession->getCustomerGroupId();
            if ($customerGroups) {
                if (!in_array(0, $customerGroups) && !$this->customerSession->isLoggedIn()) {
                    return null;
                }
                if (!in_array(0, $form->getStores()) && !in_array($store->getId(), $form->getStores())) {
                    return null;
                }
                if (!in_array($customerGroupId, $customerGroups)) {
                    return null;
                }
                if (!$form->getStatus()) {
                    return null;
                }
            }
        }
        return parent::_toHtml();
    }
}
