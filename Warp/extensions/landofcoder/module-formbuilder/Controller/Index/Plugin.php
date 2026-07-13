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

namespace Lof\Formbuilder\Controller\Index;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Exception\SessionException;

class Plugin
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var ScopeConfigInterface
     */
    protected $config;

    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * @param CustomerSession $customerSession
     * @param ScopeConfigInterface $config
     * @param RedirectInterface $redirect
     */
    public function __construct(
        CustomerSession $customerSession,
        ScopeConfigInterface $config,
        RedirectInterface $redirect
    ) {
        $this->customerSession = $customerSession;
        $this->config = $config;
        $this->redirect = $redirect;
    }

    /**
     * @param ActionInterface $subject
     * @param RequestInterface $request
     * @return void
     * @throws NotFoundException
     * @throws SessionException
     */
    public function beforeDispatch(ActionInterface $subject, RequestInterface $request)
    {
        if (!$this->customerSession->authenticate()) {
            $subject->getActionFlag()->set('', 'no-dispatch', true);
            $this->customerSession->setBeforeModuleName('formbuilder');
            $this->customerSession->setBeforeControllerName('index');
            $this->customerSession->setBeforeAction('index');
        }

        if (!$this->config->isSetFlag('lofformbuilder/general_settings/enable')) {
            throw new NotFoundException(__('Page not found.'));
        }
    }
}
