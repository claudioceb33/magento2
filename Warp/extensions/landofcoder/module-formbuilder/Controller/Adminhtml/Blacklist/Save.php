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

namespace Lof\Formbuilder\Controller\Adminhtml\Blacklist;

use Lof\Formbuilder\Controller\Adminhtml\Blacklist;
use Lof\Formbuilder\Model\BlacklistFactory;
use Lof\Formbuilder\Model\Form;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;

class Save extends Blacklist
{
    /**
     * @var BlacklistFactory
     */
    protected BlacklistFactory $blacklistFactory;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * Save constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param BlacklistFactory $blacklistFactory
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        BlacklistFactory $blacklistFactory,
        ResultFactory $resultFactory
    ) {
        parent::__construct($context, $coreRegistry);
        $this->blacklistFactory = $blacklistFactory;
        $this->resultFactory = $resultFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = $this->getRequest()->getParam('blacklist_id');
            $formId = $this->getRequest()->getParam('form_id');
            $formName = $this->getRequest()->getParam('form_name');
            $email = $this->getRequest()->getParam('email');
            $ip = $this->getRequest()->getParam('ip');
            $model = $this->blacklistFactory->create()->load($id);
            $modelBlacklist = $this->blacklistFactory->create()->getCollection();
            $checkBlacklist = $modelBlacklist->addFieldToFilter(['email','ip'], [$email,$ip])->getData();
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This blacklist no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
            if (!$email && !$ip) {
                $this->messageManager->addErrorMessage(__('Missing email or ip. You should input one of them.'));
                return $resultRedirect->setPath('*/*/');
            }
            if (!$id) {
                if ($email) {
                    $model->loadByEmail($email);
                }
                if ($ip && !$model->getId()) {
                    $model->loadByIp($ip);
                }
                if (!$model->getId()) {
                    $model->setData($data);
                }
                if (count($checkBlacklist) > 0) {
                    $this->getMessageManager()->addErrorMessage('Error: The ip or email was added to blocklist');
                    return $resultRedirect->setPath('*/blacklist/new');
                }
            } else {
                $model->setData($data);
            }
            try {
                if ($formId && !$formName) {
                    $formModel = $this->_objectManager->create(Form::class)->load($formId);
                    $formName = $formModel->getTitle();
                    $model->setFormName($formName);
                }
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the blacklist.'));
                $this->_objectManager->get(Session::class)->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['blacklist_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_objectManager->get(Session::class)->setFormData($data);
                return $resultRedirect->
                setPath('*/*/edit', ['blacklist_id' => $this->getRequest()->getParam('blacklist_id')]);
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
