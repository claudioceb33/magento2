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

namespace Lof\Formbuilder\Controller\Adminhtml\Message;

use Lof\Formbuilder\Helper\Data;
use Lof\Formbuilder\Model\Form;
use Lof\Formbuilder\Model\Reply;
use Lof\Formbuilder\Model\TransportBuilder;
use Magento\Backend\App\Action;
use Magento\Framework\App\Area;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\Store;

class Ajaxblock extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var Data
     */
    protected $helper;
    protected string $prefixSubject;

    /**
     * @param Action\Context $context
     * @param TransportBuilder $transportBuilder
     * @param JsonFactory $resultJsonFactory
     * @param StateInterface $inlineTranslation
     * @param Data $helper
     */
    public function __construct(
        Action\Context $context,
        TransportBuilder $transportBuilder,
        JsonFactory $resultJsonFactory,
        StateInterface $inlineTranslation,
        Data $helper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->inlineTranslation = $inlineTranslation;
        parent::__construct($context);
        $this->helper           = $helper;
        $this->transportBuilder  = $transportBuilder;
    }
    /**
     * Save action
     *
     * @inheritdoc
     */
    public function execute()
    {
        $responseData = [];
        $responseData['error'] = __('Don\'t have data to save.');
        $responseData['status'] = false;
        $responseData['data'] = [];
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $model = $this->_objectManager->create(Reply::class);
            $emailFrom = $this->getRequest()->getParam('email_from');
            $emailTo = $this->getRequest()->getParam('email_to');
            $formId = $this->getRequest()->getParam('form_id');
            $formName = $this->getRequest()->getParam('form_name');

            if ($emailFrom) {
                $model->loadByEmailFrom($emailFrom);
            }

            if ($emailTo) {
                $model->loadByEmailTo($emailTo);
            }
            $data['subject'] = strip_tags($data['subject']);
            $data['subject'] = @trim($data['subject']);
            $model->setData($data);
            $this->sendEmail($data);
            try {
                if ($formId && !$formName) {
                    $formModel = $this->_objectManager->create(Form::class)->load($formId);
                    $formName = $formModel->getTitle();
                    $model->setFormName($formName);
                }
                $model->save();
                $reply_id = $model->getId();
                $success_model = $model->load($reply_id);

                $responseData['status'] = true;
                $responseData['success'] = __('You saved the reply message.');
                $responseData['error'] = "";
                $responseData['data'] = $success_model->getData();

                $responseData['data']['created_time'] =
                    $this->helper->formatDateFormBuilder($responseData['data']['created_time']);
            } catch (\Exception $e) {
                $responseData['error'] = __('Have problem when save the reply message.');
            }
        }

        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($responseData)
        );
    }

    public function sendEmail($data)
    {
        $arg = [];
        $arg = $data;
        // SEND EMAIL
        $this->inlineTranslation->suspend();
        try {
            $this->transportBuilder
                ->setTemplateOptions([
                    'area' => Area::AREA_FRONTEND,
                    'store' => Store::DEFAULT_STORE_ID,
                ]);

            $this->transportBuilder->setTemplateVars($arg);
            $this->transportBuilder->setTemplateData(
                [
                'template_subject' => ($data['subject']),
                'template_text' => ($data['message']),
                ]
            );
            $emailTemplate = $this->helper->getConfig('email_settings/reply_email_template');
            $this->transportBuilder->setTemplateIdentifier($emailTemplate);
            $this->transportBuilder->setFrom($this->helper->getConfig('email_settings/sender_email_identity'));

            $this->transportBuilder->addTo($data['email_to']);

            $this->prefixSubject = '';

            $transport = $this->transportBuilder->getTransport();


            try {
                $transport->sendMessage();

                $this->inlineTranslation->resume();
                $this->messageManager->addSuccessMessage(__('Email was successfully sent.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t process your request right now. Sorry, that\'s all we know.')
                );
            }
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addErrorMessage(
                __('We can\'t process your request right now. Sorry, that\'s all we know.')
            );
        }
    }
}
