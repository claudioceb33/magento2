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
use Lof\Formbuilder\Helper\Data;
use Lof\Formbuilder\Model\Form;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;

class Ajaxblock extends Blacklist
{
    /**
     * @var Data
     */
    protected $formatDate;

    /**
     * Ajaxblock constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Data $formatDate
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Data $formatDate
    ) {
        parent::__construct($context, $coreRegistry);
        $this->formatDate = $formatDate;
    }

    /**
     * Save action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $responseData = [];
        $responseData['error'] = __('Don\'t have data to save.');
        $responseData['status'] = false;
        $responseData['data'] = [];
        // check if data sent
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $model = $this->_objectManager->create(\Lof\Formbuilder\Model\Blacklist::class);
            $email = $this->getRequest()->getParam('email');
            $ip = $this->getRequest()->getParam('ip');
            $formId = $this->getRequest()->getParam('form_id');
            $formName = $this->getRequest()->getParam('form_name');

            if ($email) {
                $model->loadByEmail($email);
            }
            if ($ip && !$model->getId()) {
                $model->loadByIp($ip);
            }
            if (!$model->getId()) {
                // init model and set data
                $model->setData($data);
                // try to save it
                try {
                    // save the data
                    if ($formId && !$formName) {
                        $formModel = $this->_objectManager->create(Form::class)->load($formId);
                        $formName = $formModel->getTitle();
                        $model->setFormName($formName);
                    }
                    $model->save();
                    $idBlacklist = $model->getData()['blacklist_id'];
                    $currentTime = $model->load($idBlacklist)->getData();

                    $responseData['status'] = true;
                    $responseData['success'] = __('You saved the blacklist.');
                    $responseData['error'] = "";
                    $responseData['created_time'] = $currentTime['created_time'];
                    $responseData['data'] = $model->getData();


                } catch (\Exception $e) {
                    $responseData['error'] = __('Have problem when save the blacklist.');
                    //$responseData['error'] .= (string)$e;
                }
            } else {
                $responseData['error'] = __('The ip or email was added to blocklist.');
            }
        }
        if (isset($responseData['data']['created_time'])) {
            $formatDate = $this->formatDate->formatDateFormBuilder($responseData['data']['created_time']);
            $responseData['data']['created_time'] = $formatDate;
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($responseData)
        );
    }
}
