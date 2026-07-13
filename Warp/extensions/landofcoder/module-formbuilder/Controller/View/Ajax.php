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

namespace Lof\Formbuilder\Controller\View;

use Lof\Formbuilder\Block\MessageView;
use Lof\Formbuilder\Helper\Data;
use Lof\Formbuilder\Model\Form;
use Lof\Formbuilder\Model\FormFactory;
use Lof\Formbuilder\Model\MessageFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Ajax extends Action
{
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param Data $helper
     * @param MessageFactory $messageFactory
     * @param FormFactory $formFactory
     * @param Session $customerSession
     * @param ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        Data $helper,
        MessageFactory $messageFactory,
        FormFactory $formFactory,
        Session $customerSession,
        ForwardFactory $resultForwardFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $registry;
        $this->helper = $helper;
        $this->messageFactory = $messageFactory;
        $this->formFactory = $formFactory;
        $this->customerSession = $customerSession;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $postData = $this->getRequest()->getPostValue();
        $qrcode = $postData["code"] ?? "";
        $guest_view = $this->helper->getConfig("message_setting/guest_view");
        $enabled_view = $this->helper->getConfig("message_setting/enabled_view");
        $item_html = "";
        $json = [];
        if (!$enabled_view || (!$guest_view && !$this->customerSession->isLoggedIn()) || empty($qrcode)) {
            $json["html"] = "";
        } else {
            $message = $this->messageFactory->create()->load($qrcode, "qrcode");
            if ($message && $message->getId()) {
                $formProfile = $this->getFormInfo($message->getFormId());
                $this->coreRegistry->register("current_message", $message);
                $this->coreRegistry->register("current_message_form", $formProfile);
                $item_html = $this->_view->getLayout()
                                    ->createBlock(MessageView::class)
                                    ->toHtml();
            }
            $json["html"] = $item_html;
        }
        if (empty($item_html)) {
            $json["url"] = $this->helper->getBaseUrl();
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($json)
        );
    }

    /**
     * @param int $formId
     * @return Form|null
     */
    protected function getFormInfo(int $formId = 0)
    {
        if ($formId) {
            return $this->formFactory->create()->load($formId);
        }
        return null;
    }
}
