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
use Magento\Customer\Model\Session;
use Magento\Customer\Model\UrlFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * url formbuilder/view/scan
 */
class Scan extends Action
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var UrlFactory
     */
    protected $urlFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helper
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helper,
        Session $customerSession,
        UrlFactory $urlFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->urlFactory = $urlFactory;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $requireLoginAccessScan = $this->helper->getConfig("message_setting/require_login_access_scan");
        $enabledView = $this->helper->getConfig("message_setting/enabled_view");
        if ($this->customerSession->isLoggedIn() || !$requireLoginAccessScan || !$enabledView) {
            $enabledBarcode = $this->helper->getConfig("message_setting/enabled_barcode");
            if ($enabledBarcode && $enabledView) {
                $page = $this->resultPageFactory->create();
                $page->getConfig()->getTitle()->prepend(__('Scan Barcode for Form'));
                $page->addHandle(['type' => 'FORMBUILDER_MESSAGE_SCAN_BARCODE']);
                return $page;
            }
            throw new NotFoundException(__('Page not found.'));
        } else {
            /** @var Redirect $resultRedirect */
            $loginUrl = $this->urlFactory->create()->getLoginUrl();
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setUrl($loginUrl);//->setPath('customer/account/login');
            return $resultRedirect;
        }
    }
}
