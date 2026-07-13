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

use Lof\Formbuilder\Helper\Data;
use Lof\Formbuilder\Model\Form;
use Lof\Formbuilder\Model\FormFactory;
use Lof\Formbuilder\Model\MessageFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
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
        $qrcode = $this->getRequest()->getParam("code");
        $guest_view = $this->helper->getConfig("message_setting/guest_view");
        $enabled_view = $this->helper->getConfig("message_setting/enabled_view");

        if (!$enabled_view || (!$guest_view && !$this->customerSession->isLoggedIn()) || empty($qrcode)) {
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }
        $page = $this->resultPageFactory->create();
        $page->getConfig()->getTitle()->prepend(__('View Form Message'));
        return $page;
    }
}
