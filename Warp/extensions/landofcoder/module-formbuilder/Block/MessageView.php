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
use Lof\Formbuilder\Model\FormFactory;
use Lof\Formbuilder\Model\Message;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Customer\Model\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class MessageView extends Template
{
    /**
     * @var null
     */
    protected $message = null;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var int|null
     */
    protected $customerId = null;

    /**
     * MessageView constructor.
     * @param Template\Context $context
     * @param FormFactory $formFactory
     * @param Registry $registry
     * @param Session $customerSession
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        FormFactory $formFactory,
        Registry $registry,
        Session $customerSession,
        \Magento\Framework\App\Http\Context $httpContext,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->addData(
            [
                'cache_lifetime' => false,
                'cache_tags' => [Message::CACHE_TAG]
            ]
        );
    }

    /**
     * get customer ID
     *
     * @return int
     */
    public function getCustomerId()
    {
        if ($this->customerId == null) {
            $this->customerId = (int)$this->customerSession->getCustomerId();
            $isLoggedIn = $this->httpContext->getValue(Context::CONTEXT_AUTH);
            if (!$isLoggedIn) {
                $this->customerId = 0;
            }
        }
        return $this->customerId;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCacheKeyInfo()
    {
        $message = $this->getCurrentMessage();
        $code = $message ? $message->getMessageId() : "";
        $code = $code ? $code : "-1";

        $customerId = (int)$this->getCustomerId();
        $customerId = $customerId ? ("c" . $customerId) : "c0";
        $code .= "-" . $customerId;

        $shortCacheId = [
            'LOF_FORMBUILDER_MESSAGE_BLOCK',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(Context::CONTEXT_GROUP),
            $this->getTemplateFile(),
            'template' => $this->getTemplate(),
            'name' => $this->getNameInLayout(),
            'base_url' => $this->getBaseUrl(),
            $code
        ];

        $cacheId = $shortCacheId;

        $shortCacheIdValues = array_values($shortCacheId);
        $shortCacheIdString = implode('|', $shortCacheIdValues);
        $shortCacheIdString = md5($shortCacheIdString);

        $cacheId['formbuilder_message_key'] = $code;
        $cacheId['short_cache_id'] = $shortCacheIdString;

        return $cacheId;
    }

    /**
     * @return mixed|null
     */
    public function getCurrentMessage()
    {
        if (!isset($this->message) || !$this->message) {
            $message = $this->registry->registry('current_message');
            $this->message = $message;
        }
        return $this->message;
    }

    /**
     * @param int $formId
     * @return \Lof\Formbuilder\Model\Form|mixed|null
     */
    public function getFormInfo(int $formId = 0)
    {
        $form = $this->registry->registry('current_message_form');
        if ($form) {
            return $form;
        }
        if ($formId) {
            return $this->formFactory->create()->load($formId);
        }
        return null;
    }

    /**
     * @param $key
     * @param string $default
     * @return array|mixed|string|null
     */
    public function getConfig($key, string $default = '')
    {
        if ($this->hasData($key)) {
            return $this->getData($key);
        }
        return $default;
    }

    /**
     * @inheritdoc
     */
    public function _toHtml()
    {
        //$enabled_qrcode = $this->helper->getConfig("message_setting/enabled_qrcode");
        //$enabled_barcode = $this->helper->getConfig("message_setting/enabled_barcode");
        $guest_view = $this->helper->getConfig("message_setting/guest_view");
        $enabled_view = $this->helper->getConfig("message_setting/enabled_view");
        $check_logged_customer = $this->helper->getConfig("message_setting/check_logged_customer");

        $message = $this->getCurrentMessage();
        $formProfile = $this->getFormInfo();

        if (!$enabled_view || (!$guest_view && !$this->customerSession->isLoggedIn()) || !$message) {
            return "";
        }
        if (!$this->getTemplate()) {
            $this->setTemplate("Lof_Formbuilder::message/view.phtml");
        }
        /* if ($formProfile) {
            $enableTracklink = $formProfile->getEnableTracklink();
        } */
        $flag = true;
        if (!$guest_view && $check_logged_customer && $message && $message->getId()) {
            $flag = $message->getCustomerId() == $this->customerSession->getCustomerId();
        }
        if ($message->getId() && $flag) {
            return parent::_toHtml();
        }
        return "";
    }
}
