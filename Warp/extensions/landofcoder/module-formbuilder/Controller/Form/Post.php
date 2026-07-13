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

namespace Lof\Formbuilder\Controller\Form;

use Exception;
use Lof\Formbuilder\Helper\Barcode;
use Lof\Formbuilder\Helper\Data;
use Lof\Formbuilder\Helper\Fields;
use Lof\Formbuilder\Model\BlacklistFactory;
use Lof\Formbuilder\Model\Form;
use Lof\Formbuilder\Model\MessageFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Module\Manager;
use Magento\Framework\Registry;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;

class Post extends Action
{
    public const FILE_TYPES = 'jpg,JPG,jpeg,JPEG,gif,GIF,png,PNG,doc,DOC,
    docx,DOCX,pdf,PDF,zip,ZIP,tar,TAR,rar,RAR,tgz,TGZ,7zip,7ZIP,gz,GZ';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var Form
     */
    protected $form;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var BlacklistFactory
     */
    protected $blacklistFactory;

    /**
     * @var Barcode
     */
    protected $barcodeHelper;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Fields
     */
    protected $formFieldHelper;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var Http
     */
    protected $httpRequest;

    /**
     * Post constructor.
     * @param Context $context
     * @param StoreManager $storeManager
     * @param PageFactory $resultPageFactory
     * @param Data $helper
     * @param ForwardFactory $resultForwardFactory
     * @param Registry $registry
     * @param StateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param Form $form
     * @param Fields $formFieldHelper
     * @param LayoutInterface $layout
     * @param Session $customerSession
     * @param Filesystem $filesystem
     * @param Http $httpRequest
     * @param Manager $moduleManager
     * @param ResourceConnection $resource
     * @param RemoteAddress $remoteAddress
     * @param DataPersistorInterface $dataPersistor
     * @param MessageFactory $messageFactory
     * @param BlacklistFactory $blacklistFactory
     * @param Barcode $barcodeHelper
     * @throws FileSystemException
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        StoreManager $storeManager,
        PageFactory $resultPageFactory,
        Data $helper,
        ForwardFactory $resultForwardFactory,
        Registry $registry,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        Form $form,
        Fields $formFieldHelper,
        LayoutInterface $layout,
        Session $customerSession,
        Filesystem $filesystem,
        Http $httpRequest,
        Manager $moduleManager,
        ResourceConnection $resource,
        RemoteAddress $remoteAddress,
        DataPersistorInterface $dataPersistor,
        MessageFactory $messageFactory,
        BlacklistFactory $blacklistFactory,
        Barcode $barcodeHelper
    ) {
        $this->storeManager = $storeManager;
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->coreRegistry = $registry;
        $this->inlineTranslation = $inlineTranslation;
        $this->form = $form;
        $this->formFieldHelper = $formFieldHelper;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->layout = $layout;
        $this->customerSession = $customerSession;
        $this->httpRequest = $httpRequest;
        $this->remoteAddress = $remoteAddress;
        $this->moduleManager = $moduleManager;
        $this->resource = $resource;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->dataPersistor = $dataPersistor;
        $this->messageFactory = $messageFactory;
        $this->blacklistFactory = $blacklistFactory;
        $this->barcodeHelper = $barcodeHelper;
        parent::__construct($context);
    }

    protected function parseSize($size): float
    {
        $size = preg_replace('/[^0-9\.]/', '', $size);
        return round($size);
    }

    /**
     * Get Data Persistor
     *
     * @return DataPersistorInterface|bool
     */
    private function getDataPersistor()
    {
        return $this->dataPersistor = ObjectManager::getInstance()
                ->get(DataPersistorInterface::class);
    }

    /**
     * Default customer account page
     *
     * @inheritdoc
     */
    public function execute()
    {
        try {
            $store = $this->storeManager->getStore();
            $mediaUrl = $this->helper->getBaseMediaUrl();
            $data = $this->getRequest()->getParams();
            if (empty($data)) {
                return $this->_redirect($store->getBaseUrl());
            }
            $currentUrl = $data['current_url'] ?? '';

            if (!$this->verifyBlacklist()) {
                return false;
            }
            if (count($data) > 2) {
                $form = $this->form->load($data['formId']);
                $fields = $form->getFields();

                if (!$this->verifyCaptcha($data, $form)) {
                    return false;
                }
                $data = $this->uploadFormFiles($data, $form, $fields, $mediaUrl);
                if (!$data) {
                    return false;
                }
                $form->escapeFormData($data);

                $customformData = $form->getCustomFormFields($data);
                $phones = [];
                $data = $this->generateMessageHtml($customformData, $data);
                $formSubmitData = [];
                if ($customformData) {
                    foreach ($customformData as $key => $val) {
                        if (isset($formSubmitData[$val['label']])) {
                            $val['label'] .= " " . $key;
                        }
                        $formSubmitData[$val['label']] = $this->helper->xssClean($val['value']);
                        if (isset($val['phone']) && $val['phone']) {
                            $phones[] = $val['phone'];
                        }
                    }
                    $this->_eventManager->dispatch('formbuilder_init_post_data', ['form_data' => $customformData]);
                }
                $checkBlacklistEmail = $this->checkBlacklistEmails($formSubmitData);
                if (!$checkBlacklistEmail) {
                    return false;
                }

                //Save message
                $messageData = [];
                $messageData['form_id'] = $form->getFormId();
                $messageData['ip_address'] = $this->remoteAddress->getRemoteAddress();
                $messageData['ip_address_long'] = $this->remoteAddress->getRemoteAddress(true);
                $messageData['customer_id'] = $this->customerSession->getCustomerId();

                $params = [];
                $params['brower'] = $this->getRequest()->getServer('HTTP_USER_AGENT');
                if ($params['brower'] && is_array($params['brower'])) {
                    $tmp = [];
                    foreach ($params['brower'] as $key => $val) {
                        $val = $this->helper->xssClean($val);
                        $tmp[$key] = $val;
                    }
                    $params['brower'] = $tmp;
                } else {
                    $params['brower'] = $this->helper->xssClean($params['brower']);
                }
                $creationTime = $this->helper->getTimezoneDateTime(); //date('Y-m-d H:i:s');
                $sender_email = $this->helper->getSenderEmail();
                $params['http_host'] = $this->getRequest()->getHttpHost();
                $params['http_host'] = $this->helper->xssClean($params['http_host']);
                $params['submit_data'] = $formSubmitData;
                $params['current_url'] = $this->helper->xssClean($currentUrl);
                $messageData['params'] = $this->helper->encodeData($params);
                $messageData['message'] = $data["message_html"] ?? "";
                $messageData['creation_time'] = $creationTime;
                $messageData['email_from'] = $sender_email;
                $messageData['form_data'] = json_encode($customformData);

                $message = $this->messageFactory->create();
                $message->setData($messageData);
                $message->save();

                /** run for hide-price module */
                $this->runFormForHidePrice($form, $data, $message);

                if ($message->getMessageId()) {
                    $this->_eventManager->dispatch('formbuilder_saved_message', ['message' => $message]);
                }

                $data["qrcode"] = $message->getQrcode();
                $data["barcode"] = $this->barcodeHelper->generateBarcodeLabel($message, false);
                $data["qrcode_tracking_link"] = $this->helper->getQrcodeTracklink($message);
                $data["track_url"] = $this->helper->getTrackUrl($message);

                $this->_eventManager->dispatch(
                    'formbuilder_submitted_message_after',
                    [
                        'object' => $this,
                        'form_data' => $customformData,
                        'send_data' => $data,
                        'track_url' => $data["track_url"],
                        'form' => $form,
                        'phones' => $phones,
                        'message' => $message
                    ]
                );

                $fieldPrefix = $this->formFieldHelper->getFieldPrefix();
                foreach ($data as $dataKey => $dataValue) {
                    if (str_contains($dataKey, $fieldPrefix)) {
                        if (is_array($dataValue)) {
                            $data[$dataKey] = implode(", ", $dataValue);
                        }
                    }
                }
                $sendEmailReturn = $this->sendNotificationEmails($form, $data, $fields);
                if (!$sendEmailReturn["flag"]) {
                    return false;
                }

                $sendThanksEmailReturn = $this->sendThanksyouEmails($form, $data, $fields);
                if (!$sendThanksEmailReturn["flag"]) {
                    return false;
                }
                $error = $sendThanksEmailReturn["error"] || $sendEmailReturn["error"];
                $status = false;
                $responseData = [];
                if (!$error) {
                    $status = true;
                    $successMessage = $form->getData('success_message');
                    if ($successMessage) {
                        $successMessage1 = $this->helper->filter($successMessage);
                        $this->messageManager->addSuccessMessage($successMessage1);
                        $responseData['message'] = $successMessage1;
                    }
                }
                $responseData['error'] = $error;
                $responseData['status'] = $status;
                $this->getDataPersistor()->clear('formbuilder');
                return $this->getResponse()->representJson(
                    $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($responseData)
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->getDataPersistor()->set('formbuilder', $this->getRequest()->getParams());
        }
    }

    /**
     * @param $message
     * @return mixed
     */
    public function getMessage($message)
    {
        return $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($message)
        );
    }

    /**
     * Verify black list
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function verifyBlacklist()
    {
        $flag = true;
        $enableBlacklist = $this->helper->getConfig('general_settings/enable_blacklist');
        if ($enableBlacklist) {
            $clientIp = $this->remoteAddress->getRemoteAddress();
            $blacklistModel = $this->blacklistFactory->create();
            if ($clientIp) {
                $blacklistModel->loadByIp($clientIp);
                if (
                    (0 < $blacklistModel->getId()) && $blacklistModel->getStatus()
                ) {
                    $responseData = [];
                    $responseData['message'] =
                        __('Your IP was blocked in our blacklist. So, we will not allow submit the form.');
                    $responseData['status'] = false;
                    $this->messageManager->addError(
                        __('Your IP was blocked in our blacklist. So, we will not allow submit the form.')
                    );
                    $this->getResponse()->representJson(
                        $this->_objectManager->
                        get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($responseData)
                    );
                    $this->getDataPersistor()->set('formbuilder', $this->getRequest()->getParams());
                    $flag = false;
                }
            }
        }
        return $flag;
    }

    /**
     * @param mixed $data
     * @param mixed $form
     * @param mixed $fields
     * @param string $mediaUrl
     * @return mixed
     * @throws Exception
     */
    public function uploadFormFiles(mixed $data, mixed $form, mixed $fields, string $mediaUrl)
    {
        $flag = true;
        $fieldPrefix = $this->helper->getFieldPrefix();
        if (!empty($fields)) {
            $mediaFolder = $this->helper->getMediaFilePath();
            $savePath = $this->helper->getUploadMediaFilePath();
            foreach ($fields as $key => $field) {
                $cid = $this->helper->getFieldId($field);
                $fieldName = $fieldPrefix . $cid . $form->getId();
                $image = $this->httpRequest->getFiles($fieldName);
                if (isset($image['error']) && $image['error'] == 0) {
                    if (empty($field)) {
                        continue;
                    }
                    if ($field['field_type'] == 'file_upload') {
                        $fieldTypes = '';
                        if (isset($field['image_type'])) {
                            $fieldTypes = $field['image_type'];
                        }
                        if (!$fieldTypes) {
                            $fieldTypes = self::FILE_TYPES;
                        }
                        $fieldTypes = str_replace(" ", "", $fieldTypes);
                        if (!is_array($fieldTypes)) {
                            $fieldTypes = explode(',', $fieldTypes);
                        }
                        $file = '';
                        $fileExists = false;
                        if ($_FILES && isset($_FILES[$fieldName])) {
                            if (file_exists($_FILES[$fieldName]['tmp_name'])) {
                                $fileExists = true;
                            }
                        }
                        if (!isset($field['required']) || (!$field['required'])) {
                            if ($_FILES && isset($_FILES[$fieldName])) {
                                if (file_exists($_FILES[$fieldName]['tmp_name'])) {
                                    $fileExists = true;
                                }
                            }
                        }
                        if (
                            !$fileExists && (!isset($field['required']) ||
                                (!$field['required']))
                        ) {
                            $file = '';
                        } else {
                            $uploader = $this->_objectManager->create(
                                \Magento\Framework\File\Uploader::class,
                                ['fileId' => $fieldName]
                            );
                            $uploader->setAllowedExtensions($fieldTypes);
                            $uploader->setAllowRenameFiles(true);
                            $uploader->setFilesDispersion(false);
                            $file = $uploader->save($savePath);
                        }
                        if ($file && empty($file)) {
                            continue;
                        }

                        if ($file) {
                            try {
                                $field_label = $field['label'] ?? '';

                                $imageMaximumSize = $this->parseSize(@ini_get('upload_max_filesize'));
                                if ($imageMaximumSize <= 0) {
                                    $imageMaximumSize = 2;
                                }
                                if (isset($field['image_maximum_size']) && $field['image_maximum_size']) {
                                    $imageMaximumSize = $field['image_maximum_size'];
                                }

                                if (
                                    isset($field['image_maximum_size']) &&
                                    ($imageMaximumSize * 1024 * 1024) < $file['size']
                                ) {
                                    $this->messageManager->addErrorMessage(
                                        __($field_label . ' - The file is too big.')
                                    );
                                    $this->mediaDirectory->delete('lof/formbuilder/files/' . $file['file']);
                                    $this->getDataPersistor()->set(
                                        'formbuilder',
                                        $this->getRequest()->getParams()
                                    );
                                    $flag = false;
                                    break;
                                }

                                $imgExtens = ["gif", "jpeg", "jpg", "png"];
                                $temp = explode(".", $file['file']);
                                $extension = end($temp);
                                $data[$fieldName] = $fieldName;
                                $data[$fieldName . '_filename'] = $file['file'];
                                $data[$fieldName . '_fileurl'] = $mediaUrl . $mediaFolder . '/' . $file['file'];
                                $data[$fieldName . '_filesize'] = $file['size'];
                                if (in_array($extension, $imgExtens)) {
                                    $data[$fieldName . '_isimage'] = true;
                                }
                            } catch (\Exception $e) {
                                if ($this->getRequest()->isAjax()) {
                                    $responseData = [];
                                    $responseData['message'] = $field_label . ' - ' . $e->getMessage();
                                    $responseData['status'] = false;
                                    $this->getResponse()->representJson(
                                        $this->_objectManager->
                                        get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($responseData)
                                    );
                                }
                                $this->messageManager->addError($field_label . ' - ' . $e->getMessage());
                                $this->getDataPersistor()->set('formbuilder', $this->getRequest()->getParams());
                                $this->_redirect($data['return_url']);
                                $flag = false;
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $flag ? $data : false;
    }

    /**
     * @param $data
     * @return bool
     * @throws NoSuchEntityException
     */
    public function verifyCaptcha($data)
    {
        $flag = true;
        $post     = $this->getRequest()->getPostValue();
        // reCaptcha
        if (isset($post['g-recaptcha-response']) && ((int)$post['g-recaptcha-response']) === 0) {
            $flag = false;
            if ($this->getRequest()->isAjax()) {
                $this->messageManager->addErrorMessage(__('Please check reCaptcha and try again.'));
            } else {
                $this->messageManager->addErrorMessage(__('Please check reCaptcha and try again.'));
                $this->_redirect($data['return_url']);
                $this->getDataPersistor()->set('formbuilder', $this->getRequest()->getParams());
            }
        }
        if (isset($post['g-recaptcha-response'])) {
            $flag = false;
            $captcha = $post['g-recaptcha-response'];
            $secretKey = $this->helper->getConfig('general_settings/captcha_privatekey');
            $ip = $this->remoteAddress->getRemoteAddress();

            $postData = http_build_query([
                'secret'   => $secretKey,
                'response' => $captcha,
                'remoteip' => $ip
            ]);

            $opts = [
                'http' => [
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $postData
                ]
            ];

            $context  = stream_context_create($opts);
            $response = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
            $result   = @json_decode($response);

            $flag = $result && isset($result->success) ? $result->success : false;
            if (!$flag) {
                if ($this->getRequest()->isAjax()) {
                    $responseData = [];
                    $responseData['message'] = __('Please check reCaptcha and try again.');
                    $responseData['status'] = false;
                    $this->getResponse()->representJson(
                        $this->_objectManager->get(Magento\Framework\Json\Helper\Data::class)->jsonEncode($responseData)
                    );
                    $this->messageManager->addErrorMessage(__('Please check reCaptcha and try again.'));
                } else {
                    $this->messageManager->addErrorMessage(__('Please check reCaptcha and try again.'));
                    $this->getDataPersistor()->set('formbuilder', $this->getRequest()->getParams());
                }
            }
        }
        return $flag;
    }

    /**
     * @param $form
     * @param $data
     * @param $fields
     * @return bool[]
     * @throws NoSuchEntityException
     */
    public function sendNotificationEmails($form, $data, $fields)
    {
        $this->inlineTranslation->suspend();

        $flag = true;
        $error = false;
        $store = $this->storeManager->getStore();
        $fieldPrefix = $this->helper->getFieldPrefix();
        $emails = $form->getData('email_receive');
        $currentUrl = $data['current_url'] ?? '';
        $reply_email_arr = [];
        if ($fields) {
            foreach ($fields as $fitem) {
                $fieldType = $fitem['field_type'] ?? "";
                $cid = $this->helper->getFieldId($fitem);
                $fieldId = $fieldPrefix . $cid . $form->getId();
                if (
                    $fieldType == "email" && isset($data[$fieldId]) &&
                    $data[$fieldId] && $this->helper->validateEmailAddress($data[$fieldId])
                ) {
                    $reply_email_arr[] = @trim($data[$fieldId]);
                }
            }
            //$reply_emails = implode(",", $reply_email_arr);
        }
        if (@trim($emails) != '') {
            $fromAddress = $this->helper->getConfig('email_setting/sender_email_identity');
            $fromAddress = $fromAddress === null ? 'general' : $fromAddress;
            //If form have custom sender, will get email and name on the submitted form data
            $senderEmailField = $form->getData('sender_email_field');
            $senderNameField = $form->getData('sender_name_field');
            if ($senderEmailField) {
                $senderEmailField = $this->formFieldHelper->getFieldPrefix() . $senderEmailField . $form->getId();
                if (
                    isset($data[$senderEmailField]) && $data[$senderEmailField] &&
                    $this->helper->validateEmailAddress($data[$senderEmailField])
                ) {
                    $fromAddress = ['email' => $data[$senderEmailField]];

                    $senderNameField = $this->formFieldHelper->getFieldPrefix() . $senderNameField . $form->getId();
                    if (isset($data[$senderNameField]) && $data[$senderNameField]) {
                        $fromAddress['name'] = $data[$senderNameField];
                    }
                }
            }

            $emails = explode(',', $emails);
            $this->_eventManager->dispatch(
                'formbuilder_init_email_data',
                ['data' => $data, 'form' => $form, 'from_address' => $fromAddress, 'emails' => $emails]
            );

            foreach ($emails as $v) {
                try {
                    $postObject = new DataObject();
                    $data['form_id'] = $form->getId();
                    $data['title'] = $form->getData('title');
                    $data['current_url'] = $currentUrl;
                    $tags = $form->getTags();
                    if ($tags) {
                        $tags_array = explode(",", $tags);
                        $newTags = [];
                        foreach ($tags_array as $_tag) {
                            $newTags[] = @trim($_tag);
                        }
                        $data['tags'] = implode(" , ", $newTags);
                    }
                    $postObject->setData($data);
                    $v = @trim($v);
                    if ($this->helper->validateEmailAddress($v)) {
                        $transportBuilder = $this->transportBuilder
                            ->setTemplateIdentifier($form->getData('email_template'))
                            ->setTemplateOptions(
                                [
                                    'area' => 'frontend',
                                    'store' => $store->getId()
                                ]
                            )
                            ->setTemplateVars(['data' => $postObject])
                            ->setFrom($fromAddress)
                            ->addTo($v);
                        if (is_array($fromAddress) && isset($fromAddress['email']) && $fromAddress['email']) {
                            $transportBuilder->setReplyTo($fromAddress['email']);
                        } else {
                            if ($reply_email_arr) {
                                $transportBuilder->setReplyTo($reply_email_arr[0]);
                            }
                        }

                        $transport = $transportBuilder->getTransport();
                        try {
                            $transport->sendMessage();
                            $this->inlineTranslation->resume();
                        } catch (\Exception $e) {
                            $error = true;
                            $this->helper->writeLogData($e);
                            if ($this->getRequest()->isAjax()) {
                                $responseData = [];
                                $responseData['message'] = __(
                                    'An error when send email. We can\'t process your request right now.
                                    Sorry, that\'s all we know.'
                                );
                                $responseData['message'] .= $e->getMessage();
                                $responseData['status'] = false;
                                $this->getResponse()->representJson(
                                    $this->_objectManager
                                        ->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($responseData)
                                );
                            }
                            $this->messageManager->addErrorMessage(
                                __('An error when send email.
                                We can\'t process your request right now. Sorry, that\'s all we know.')
                            );
                            $this->getDataPersistor()->set('formbuilder', $this->getRequest()->getParams());
                        }
                    }
                } catch (\Exception $e) {
                    $this->inlineTranslation->resume();
                    $this->helper->writeLogData($e);
                    if ($this->getRequest()->isAjax()) {
                        $responseData = [];
                        $responseData['message'] = __('An error when send email.
                        We can\'t process your request right now. Sorry, that\'s all we know.');
                        $responseData['message'] .= $e->getMessage();
                        $responseData['status'] = false;
                        $this->getResponse()->representJson(
                            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($responseData)
                        );
                    }
                    $this->messageManager->addErrorMessage(
                        __('Errors when send emails.
                        We can\'t process your request right now. Sorry, that\'s all we know.')
                    );
                    $this->getDataPersistor()->set('formbuilder', $this->getRequest()->getParams());
                    $flag = false;
                    break;
                }
            }
        }
        return [
            "flag" => $flag,
            "error" => $error
        ];
    }

    /**
     * send thanksyou emails
     *
     * @param mixed $form
     * @param mixed $data
     * @param mixed $fields
     * @return mixed
     */
    public function sendThanksyouEmails($form, $data, $fields)
    {
        $this->inlineTranslation->suspend();

        $error = false;
        $flag = true;
        $store = $this->storeManager->getStore();
        $fieldPrefix = $this->helper->getFieldPrefix();
        $field = $form->getData('thankyou_field');
        $sendThanksEmail = $this->helper->getConfig('email_settings/send_thanks_email');
        $thanksEmailAll = $this->helper->getConfig('email_settings/thanks_email_all');

        if ($sendThanksEmail && $thanksEmailAll && $fields) { //Send thanks you email to all email fields

            foreach ($fields as $fitem) {
                $fieldType = isset($fitem['field_type']) ? $fitem['field_type'] : "";
                $cid = $this->helper->getFieldId($fitem);
                $fieldId = $fieldPrefix . $cid . $form->getId();
                if ($fieldType == "email" && isset($data[$fieldId]) && $data[$fieldId] && $this->helper->validateEmailAddress($data[$fieldId]) && $form->getData('thankyou_email_template')) {
                    try {
                        $postObject = new DataObject();
                        $data['form_id'] = $form->getFormId();
                        $data['title'] = $form->getData('title');
                        $data[$fieldId] = @trim($data[$fieldId]);
                        $postObject->setData($data);
                        $transport = $this->transportBuilder
                            ->setTemplateIdentifier($form->getData('thankyou_email_template'))
                            ->setTemplateOptions(
                                [
                                    'area' => 'frontend',
                                    'store' => $store->getId()
                                ]
                            )
                            ->setTemplateVars(['data' => $postObject])
                            ->setFrom($this->helper->getConfig('email_settings/sender_email_identity'))
                            ->addTo($data[$fieldId])
                            ->getTransport();
                        try {
                            $transport->sendMessage();
                            $this->inlineTranslation->resume();
                        } catch (\Exception $e) {
                            $error = true;
                            $this->helper->writeLogData($e);
                            if ($this->getRequest()->isAjax()) {
                                $responseData = [];
                                $responseData['message'] = __('An error when send email. We can\'t process your request right now. Sorry, that\'s all we know.');
                                $responseData['message'] .= $e->getMessage();
                                $responseData['status'] = false;
                                $this->getResponse()->representJson(
                                    $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($responseData)
                                );
                            }
                            $this->messageManager->addError(
                                __('An error when send thanks you email. We can\'t process your request right now. Sorry, that\'s all we know.')
                            );
                            $this->getDataPersistor()->set('formbuilder', $this->getRequest()->getParams());
                        }
                    } catch (\Exception $e) {
                        $this->helper->writeLogData($e);
                        $this->inlineTranslation->resume();
                        if ($this->getRequest()->isAjax()) {
                            $responseData = [];
                            $responseData['message'] = __('An error when send email. We can\'t process your request right now. Sorry, that\'s all we know.');
                            $responseData['message'] .= $e->getMessage();
                            $responseData['status'] = false;
                            $this->getResponse()->representJson(
                                $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($responseData)
                            );
                        }
                        $this->messageManager->addError(
                            __('Errors when send thanks you emails. We can\'t process your request right now. Sorry, that\'s all we know.')
                        );
                        $this->getDataPersistor()->set('formbuilder', $this->getRequest()->getParams());
                        $flag = false;
                        break;
                    }
                }
            }
        } elseif ($field && $sendThanksEmail) { //Send thanks you email to selected email field

            $field = $this->formFieldHelper->getFieldPrefix() . $field . $form->getId();
            if (isset($data[$field]) && $data[$field] && $this->helper->validateEmailAddress($data[$field])) {
                try {
                    $postObject = new DataObject();
                    $data['form_id'] = $form->getId();
                    $data['title'] = $form->getData('title');
                    $data[$field] = @trim($data[$field]);
                    $postObject->setData($data);
                    //$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                    $transport = $this->transportBuilder
                        ->setTemplateIdentifier($form->getData('thankyou_email_template'))
                        ->setTemplateOptions(
                            [
                                'area' => 'frontend',
                                'store' => $store->getId()
                            ]
                        )
                        ->setTemplateVars(['data' => $postObject])
                        ->setFrom($this->helper->getConfig('email_settings/sender_email_identity'))
                        ->addTo($data[$field])
                        ->getTransport();
                    try {
                        $transport->sendMessage();
                        $this->inlineTranslation->resume();
                    } catch (\Exception $e) {
                        $error = true;
                        $this->helper->writeLogData($e);
                        if ($this->getRequest()->isAjax()) {
                            $responseData = [];
                            $responseData['message'] = __('An error when send email. We can\'t process your request right now. Sorry, that\'s all we know.');
                            $responseData['message'] .= $e->getMessage();
                            $responseData['status'] = false;
                            $this->getResponse()->representJson(
                                $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($responseData)
                            );
                        }
                        $this->messageManager->addErrorMessage(
                            __('An error when send a thanks you email. We can\'t process your request right now. Sorry, that\'s all we know.')
                        );
                        $this->getDataPersistor()->set('formbuilder', $this->getRequest()->getParams());
                    }
                } catch (\Exception $e) {
                    $this->helper->writeLogData($e);
                    $this->inlineTranslation->resume();
                    if ($this->getRequest()->isAjax()) {
                        $responseData = [];
                        $responseData['message'] = __('An error when send email. We can\'t process your request right now. Sorry, that\'s all we know.');
                        $responseData['status'] = false;
                        $this->getResponse()->representJson(
                            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($responseData)
                        );
                    }
                    $this->messageManager->addErrorMessage(
                        __('An error when send a thanks you emails. We can\'t process your request right now. Sorry, that\'s all we know.')
                    );
                    $this->getDataPersistor()->set('formbuilder', $this->getRequest()->getParams());
                    $flag = false;
                }
            }
        }
        return [
            "flag" => $flag,
            "error" => $error
        ];
    }

    /**
     * Check emails in submitted data is in blacklist
     *
     * @param mixed $formSubmitData
     * @return bool
     */
    public function checkBlacklistEmails($formSubmitData)
    {
        $flag = true;
        $enableBlacklist = $this->helper->getConfig('general_settings/enable_blacklist');
        if ($formSubmitData && $enableBlacklist) {
            $emails = $this->helper->getEmailsFromData($formSubmitData);
            if ($emails) {
                $blacklistCollection = $this->blacklistFactory->create()->getCollection()->addFieldToFilter(
                    "status",
                    1
                );
                $blacklistCollection->addEmailsToFilter($emails);

                if ($blacklistCollection->getSize()) {
                    $responseData = [];
                    $responseData['message'] = __('One or more emails in form were blocked in our blacklist. So, we will not allow submit the form.');
                    $responseData['status'] = false;
                    $this->messageManager->addErrorMessage(__('One or more emails in form were blocked in our blacklist. So, we will not allow submit the form.'));
                    $this->getDataPersistor()->set('formbuilder', $this->getRequest()->getParams());
                    $this->getResponse()->representJson(
                        $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($responseData)
                    );

                    $flag = false;
                }
            }
        }
        return $flag;
    }

    /**
     * generate message html content
     *
     * @param mixed $customformData
     * @param mixed $data
     * @return mixed
     */
    public function generateMessageHtml($customformData, $data)
    {
        $creationTime = $this->helper->getTimezoneDateTime(); //date('Y-m-d H:i:s');
        $messageHtml = $this->layout->createBlock('\Magento\Framework\View\Element\Template')
            ->setTemplate("Lof_Formbuilder::email/items.phtml")
            ->setCustomFormData($customformData)
            ->setCreationTime($creationTime)
            ->toHtml();

        $show_all_fields = $this->helper->getConfig('email_settings/show_all_fields');
        if (!$show_all_fields) {
            $data['message'] = $this->layout->createBlock('\Magento\Framework\View\Element\Template')
                ->setTemplate("Lof_Formbuilder::email/items_check_enable.phtml")
                ->setCustomFormData($customformData)
                ->setCreationTime($creationTime)
                ->toHtml();
        } else {
            $data['message'] = $messageHtml;
        }
        $data["message_html"] = $messageHtml;
        return $data;
    }

    /**
     * run form for hide price compatible
     *
     * @param mixed $data
     * @param \Lof\Formbuilder\Model\Message $message
     * @return void
     */
    public function runFormForHidePrice($data, $message)
    {
        if ($this->moduleManager->isEnabled('Lof_HidePrice') && isset($data['hideprice_id']) && $data['hideprice_id']) {
            try {
                $connection = $this->resource->getConnection();
                $table = $this->resource->getTableName('lof_hideprice_hideprice_message');
                $_data = [];
                $_data[] = [
                    'hideprice_id' => $data['hideprice_id'],
                    'entity_id' => $data['entity_id'],
                    'message_id' => $message->getMessageId()
                ];
                $connection->insertMultiple($table, $_data);
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
    }
}
