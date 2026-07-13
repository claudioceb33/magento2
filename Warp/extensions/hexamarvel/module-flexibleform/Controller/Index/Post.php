<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Hexamarvel\FlexibleForm\Helper\Data as Helper;
use Hexamarvel\FlexibleForm\Model\FormDataFactory;
use Hexamarvel\FlexibleForm\Model\FormFactory;
use Hexamarvel\FlexibleForm\Model\FieldFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Area;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\CountryFactory;
use Hexamarvel\FlexibleForm\Model\FileUploader;
use Magento\Captcha\Helper\Data;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Captcha\Observer\CaptchaStringResolver;

class Post extends \Magento\Framework\App\Action\Action
{
    /**
     * Admin Email configuartion
     */
    const XML_PATH_ADMIN_EMAIL_SENDER      = 'hexaform/admin_email_settings/admin_email';
    const XML_PATH_ADMIN_EMAIL_SUBJECT     = 'hexaform/admin_email_settings/email_subject';
    const XML_PATH_ADMIN_GENERAL_EMAIL     = 'trans_email/ident_support/email';
    const XML_PATH_ADMIN_GENERAL_NAME     = 'trans_email/ident_support/name';

    /**
     * Customer Email configuartion
     */
    const XML_PATH_CUSTOMER_EMAIL_SENDER      = 'hexaform/customer_email_settings/customer_reply_to';
    const XML_PATH_CUSTOMER_EMAIL_TEMPLATE    = 'hexaform/customer_email_settings/email_template';
    const XML_PATH_CUSTOMER_EMAIL_SUBJECT     = 'hexaform/customer_email_settings/email_subject';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var FormDataFactory
     */
    protected $formDataFactory;

    /**
     * @var FormCollection
     */
    protected $formCollection;

    /**
     * @var fieldFactory
     */
    protected $fieldFactory;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var RegionFactory
     */
    protected $regionFactory;

    /**
     * @var CountryFactory
     */
    protected $countryFactory;

    /**
     * @var FileUploader
     */
    protected $fileUploader;

    /**
     * @var Data
     */
    protected $captchaHelper;

    /**
     * @var ActionFlag
     */
    protected $actionFlag;

    /**
     * @var CaptchaStringResolver
     */
    protected $captchaStringResolver;

    /**
     * @var dataPersistor
     */
    protected $dataPersistor;

    /**
     * Post constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Helper $helper
     * @param FormDataFactory $formDataFactory
     * @param FormFactory $formCollection
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param RegionFactory $regionFactory
     * @param CountryFactory $countryFactory
     * @param FileUploader $fileUploader
     * @param Data $helper
     * @param ActionFlag $actionFlag
     * @param CaptchaStringResolver $captchaStringResolver
     */
    public function __construct(
        Context $context,
        ResultFactory $resultPageFactory,
        Helper $helper,
        FormDataFactory $formDataFactory,
        FormFactory $formCollection,
        FieldFactory $fieldFactory,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        RegionFactory $regionFactory,
        CountryFactory $countryFactory,
        FileUploader $fileUploader,
        Data $captchaHelper,
        ActionFlag $actionFlag,
        CaptchaStringResolver $captchaStringResolver,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->helper            = $helper;
        $this->formDataFactory   = $formDataFactory;
        $this->formCollection    = $formCollection;
        $this->transportBuilder  = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig       = $scopeConfig;
        $this->storeManager      = $storeManager;
        $this->fieldFactory      = $fieldFactory;
        $this->regionFactory     = $regionFactory;
        $this->countryFactory    = $countryFactory;
        $this->fileUploader      = $fileUploader;
        $this->captchaHelper     = $captchaHelper;
        $this->_actionFlag       = $actionFlag;
        $this->captchaStringResolver = $captchaStringResolver;
        $this->_objectManager    = $objectmanager;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {

        try {
            //$page = $this->resultPageFactory->create();
            $imageArray = $this->getRequest()->getFiles('image_option');
            $fileArray = $this->getRequest()->getFiles('file_option');
            $videoArray = $this->getRequest()->getFiles('video_option');
            $allfilesArray = $this->getRequest()->getFiles('all_files_option');


            $post = $this->getRequest()->getParams();
            $formData = $this->formDataFactory->create();
            $formDetails = $this->getFormDetails($post);
            $captchaEnabled = $formDetails->getCaptcha();
            $inquiryForm = 0;
            if ($this->getEmailInfo('hexaform/product_inquiry_settings/enable_enquiry')) {
                $inquiryForm = $this->getEmailInfo('hexaform/product_inquiry_settings/form_lists');
            }

            if ($captchaEnabled && $inquiryForm !== $formDetails->getId()) {
                if ($this->helper->getConfig(
                    'hexaform/captcha_option/captcha_type'
                ) == 'google') {
                    $captcha = $this->getRequest()->getParam('g-recaptcha-response');
                    $secret = $this->helper->getConfig('hexaform/captcha_option/secret_key'); //Replace with your secret key
                    $response = null;
                    $path = 'https://www.google.com/recaptcha/api/siteverify?';
                    $dataC =  [
                        'secret' => $secret,
                        'remoteip' => $_SERVER["REMOTE_ADDR"],
                        'v' => 'php_1.0',
                        'response' => $captcha
                    ];
                    $req = "";
                    foreach ($dataC as $key => $value) {
                        $req .= $key . '=' . urlencode(stripslashes($value)) . '&';
                    }
                    // Cut the last '&'
                    $req = substr($req, 0, strlen($req)-1);
                    $response = file_get_contents($path . $req);
                    $answers = json_decode($response, true);
                    if (trim($answers ['success']) == true) {
                    } else {
                        $this->messageManager->addErrorMessage('Captcha verification Failed');
                        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                        return $resultRedirect;
                    }
                } else {
                    $formId = 'form_builder';
                    $captcha = $this->captchaHelper->getCaptcha($formId);
                    if ($captcha->isRequired()) {
                        /** @var \Magento\Framework\App\Action\Action $controller */
                        if (!$captcha->isCorrect($this->captchaStringResolver->resolve($this->getRequest(), $formId))) {
                            $this->messageManager->addError(__('Incorrect CAPTCHA.'));
                            $this->getDataPersistor()->set($formId, $this->getRequest()->getPostValue());
                            $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                            return $resultRedirect;
                        }
                    }
                }
            }

            $fileUploadFields = $this->fieldFactory->create()->getCollection()
                                ->addFieldToFilter('is_active', '1')
                                ->addFieldToFilter('field_type', ['in' => ['image', 'file', 'video', 'all_files']])
                                ->addFieldToFilter('form_id', $formDetails->getId());

            foreach ($fileUploadFields as $key => $fieldsUpload) {
                if ($fieldsUpload->getFieldType() == 'image') {
                    $allowedTypes = ['image/jpg', 'image/jpeg', 'image/gif', 'image/png'];
                    $enabledTypes = ['jpg', 'jpeg', 'gif', 'png'];
                    $size = '1024000';
                    $fileId = 'image_option['.$fieldsUpload->getId().']';
                    $errorMessage = __('Uploaded Image is greater than 1024 KB');
                    if (empty($imageArray[$fieldsUpload->getId()]['name'])) {
                        continue;
                    }
                } elseif ($fieldsUpload->getFieldType() == 'file') {
                    $allowedTypes = [
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/pdf'
                    ];
                    $enabledTypes = ['doc', 'docx', 'xls', 'xlsx', 'pdf', 'csv'];
                    $size = '5242880';
                    $fileId = 'file_option['.$fieldsUpload->getId().']';
                    $errorMessage = __('Uploaded File is greater than 5 MB');
                    if (empty($fileArray[$fieldsUpload->getId()]['name'])) {
                        continue;
                    }
                } elseif ($fieldsUpload->getFieldType() == 'video') {
                    $allowedTypes = ['video/mp4', 'video/webm', 'video/flv', 'video/mov', 'video/avi', 'video/mpeg'];
                    $enabledTypes = ['mp4', 'webm', 'flv', 'mov', 'avi', 'mpeg'];
                    $size = '52428800';
                    $fileId = 'video_option['.$fieldsUpload->getId().']';
                    $errorMessage = __('Uploaded Video is greater than 50 MB');
                    if (empty($videoArray[$fieldsUpload->getId()]['name'])) {
                        continue;
                    }
                } else {
                    $allowedTypes = [
                        'image/jpg', 'image/jpeg', 'image/gif', 'image/png',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/pdf',
                        'video/mp4', 'video/webm', 'video/flv', 'video/mov', 'video/avi', 'video/mpeg'
                    ];
                    $enabledTypes = [
                        'jpg', 'jpeg', 'gif', 'png',
                        'doc', 'docx', 'xls', 'xlsx', 'pdf', 'csv',
                        'mp4', 'webm', 'flv', 'mov', 'avi', 'mpeg'
                    ];
                    $size = '52428800';
                    $fileId = 'all_files_option['.$fieldsUpload->getId().']';
                    $errorMessage = __('Uploaded Image is greater than 50 MB');
                    if (empty($allfilesArray[$fieldsUpload->getId()]['name'])) {
                        continue;
                    }
                }

                $uploader = $this->_objectManager->create('Magento\MediaStorage\Model\File\Uploader', [
                    'fileId' => $fileId
                ]);

                $file = $uploader->validateFile();

                if (!in_array($file['type'], $allowedTypes)) {
                    $this->messageManager->addErrorMessage('Disallowed file type');
                    $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                    $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                    return $resultRedirect;
                }
                if (isset($file['name']) && !empty($file['name'])) {
                    if ($file['size'] <= $size) {
                        $path = $this->fileUploader->uploadFiles($fileId, $enabledTypes);
                        if ($path) {
                            $post['field'][$fieldsUpload->getId()] = $path;
                        }
                    } else {
                        $this->messageManager->addErrorMessage($errorMessage);
                        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                        return $resultRedirect;
                    }
                }
            }

            $finalData = [];
            if (isset($post['field'])) {
                foreach ($post['field'] as $key => $value) {
                    $field = $this->fieldFactory->create()->load($key);
                    if ($field->getFieldType() != 'terms') {
                        $finalData[$key] = $value;
                    }
                }
            }

            if (isset($post['option'])) {
                foreach ($post['option'] as $key => $value) {
                    $field = $this->fieldFactory->create()->load($key);
                    if ($field->getFieldType() != 'terms') {
                        if ($field->getFieldType() == 'state') {
                            if (ctype_digit($value)) {
                                $region = $this->regionFactory->create()->load($value);
                                $finalData[$key] = $region->getName();
                            }
                        } elseif ($field->getFieldType() == 'country') {
                            $country = $this->countryFactory->create()->load($value);
                            $finalData[$key] = $country->getName();
                        } elseif ($field->getFieldType() == 'multiselect' ||
                            $field->getFieldType() == 'checkbox' ||
                            $field->getFieldType() == 'radio'
                        ) {
                            $finalData[$key] = implode(', ', $value);
                        } else {
                            $finalData[$key] = $value;
                        }
                    }
                }
            }
            if (empty($finalData)) {
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $resultRedirect;
            }

            $data['form_id'] = $post['form_id'];
            $data['value'] = json_encode($finalData);
            $formData->setData($data)->save();

            try {
                $this->sendEmail($formData);
            } catch (LocalizedException $e) {
                $errorMessage = $e->getMessage();
                if ($formDetails->getFailureMessage()) {
                    $errorMessage = $formDetails->getFailureMessage();
                }

                $this->messageManager->addErrorMessage($errorMessage);
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $resultRedirect;
            }

            if ($formDetails->getSuccessMessage()) {
                $this->messageManager->addSuccessMessage($formDetails->getSuccessMessage());
            } else {
                $this->messageManager->addSuccessMessage(__('Form Submitted successfully!'));
            }


            $resirectUrl = $this->_redirect->getRefererUrl();
            if ($formDetails->getRedirectUrl()) {
                $resirectUrl = $formDetails->getRedirectUrl();
            }

            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($resirectUrl);
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
    }

    /**
     * @return obj
     */
    private function getDataPersistor()
    {
        if ($this->dataPersistor === null) {
            $this->dataPersistor = ObjectManager::getInstance()
                ->get(DataPersistorInterface::class);
        }

        return $this->dataPersistor;
    }

    /**
     * @param array $post
     * @return obj
     */
    public function getFormDetails($post)
    {
        $formDetails = $this->formCollection->create()->load($post['form_id']);
        return $formDetails;
    }


    /**
     * @param array $post Post data from Flexible form
     * @return void
     */
    protected function sendEmail($formData)
    {
        $formDetails = $this->getFormDetails($formData);
        $customerEmailField  = $formDetails->getEmailField();
        $customerEmail = '';
        $senderEmail = [];
        $formValues = json_decode($formData['value'], true);
        if (empty($formValues)) {
            return;
        }

        $adminEmailSubject = $this->getEmailInfo(self::XML_PATH_ADMIN_EMAIL_SUBJECT);
        $customerEmailSubject = $this->getEmailInfo(self::XML_PATH_CUSTOMER_EMAIL_SUBJECT);
        $templateVars = [
            'data'  => $formValues,
            'admin_subject' => $adminEmailSubject ? $adminEmailSubject : __('Flexible Form') ,
            'customer_subject' => $customerEmailSubject ? $customerEmailSubject : __('Flexible Form')
        ];

        /* Admin Notification */
        $adminNotificationEnabled = $formDetails->getAdminEmailActive();
        if ($adminNotificationEnabled) {
            $replyToEmail = $this->getEmailInfo(self::XML_PATH_ADMIN_GENERAL_EMAIL);
            $senderEmail = [
                'email' => $this->getEmailInfo(self::XML_PATH_ADMIN_GENERAL_EMAIL),
                'name'  => $this->getEmailInfo(self::XML_PATH_ADMIN_GENERAL_NAME)
            ];
            $this->sendAdminNotification($formDetails, $templateVars, $senderEmail, $replyToEmail);
        }

        if ($customerEmailField && isset($formValues[$customerEmailField])) {
            $customerEmail = $formValues[$customerEmailField];
        } else {
            return;
        }

        /* Customer Notification */
        $customerNotification = $formDetails->getCustomerEmailActive();
        if ($customerNotification) {
            $this->sendCustomerNotification($formDetails, $templateVars, $customerEmail);
        }
    }

    /**
     * @param array $formDetails
     * @param object $templateVars
     * @param array $senderEmail
     * @param string $customerEmail
     * @return void
     */
    protected function sendAdminNotification($formDetails, $templateVars, $senderEmail, $replyToEmail)
    {
        $adminEmail = $formDetails->getAdminEmail();
        if (empty($adminEmail)) {
            $configEmail = $this->getEmailInfo(self::XML_PATH_ADMIN_EMAIL_SENDER);
            $adminEmail = $this->getEmailInfo('trans_email/ident_'.$configEmail.'/email');
        }

        $adminEmailTemplate = 'hexaform_admin_email_settings_email_template';

        try {
            $this->send($replyToEmail, '', $templateVars, $adminEmailTemplate, $senderEmail, $adminEmail);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }

    /**
     * Send Admin Email from Flexible form
     *
     * @param array $formDetails
     * @param object $templateVars
     * @param string $customerEmail
     * @return void
     */
    protected function sendCustomerNotification($formDetails, $templateVars, $customerEmail)
    {
        $customerEmailTemplate = $this->getEmailInfo(self::XML_PATH_CUSTOMER_EMAIL_TEMPLATE);
        $customerReplyToEmail = $formDetails->getCustomerToEmail();
        if (empty($customerReplyToEmail)) {
            $customerEmailReply = $this->getEmailInfo(self::XML_PATH_CUSTOMER_EMAIL_SENDER);
            $customerReplyToEmail = $this->getEmailInfo('trans_email/ident_'.$customerEmailReply.'/email');
        }

        $configEmail = $this->getEmailInfo(self::XML_PATH_ADMIN_EMAIL_SENDER);
        $adminSender = $this->getEmailInfo('trans_email/ident_'.$configEmail.'/email');
        $adminName = $this->getEmailInfo('trans_email/ident_'.$configEmail.'/name');

        $senderEmail = [
            'email' => $adminSender,
            'name'  => $adminName
        ];

        try {
            $this->send($customerReplyToEmail, $adminName, $templateVars, $customerEmailTemplate, $senderEmail, $customerEmail);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }

    /**
     * @param string $replyTo
     * @param string $replyToName
     * @param array $templateVars
     * @param string $emailTemplate
     * @param array $sender
     * @param string $recipient
     * @return void
     */
    private function send($replyTo, $replyToName, $templateVars, $emailTemplate, $sender, $recipient)
    {
        if (!empty($sender) && !empty($recipient)) {
            $this->inlineTranslation->suspend();
            try {
                $transport = $this->transportBuilder
                    ->setTemplateIdentifier($emailTemplate)
                    ->setTemplateOptions(
                        [
                            'area' => Area::AREA_FRONTEND,
                            'store' => $this->storeManager->getStore()->getId()
                        ]
                    )
                    ->setTemplateVars($templateVars)
                    ->setFrom($sender)
                    ->addTo($recipient)
                    ->setReplyTo($replyTo, $replyToName)
                    ->getTransport();

                $transport->sendMessage();
            } finally {
                $this->inlineTranslation->resume();
            }
        }
    }

    /**
     * Get Email config details
     * @param string $path
     * @param string $scope
     */
    public function getEmailInfo($path, $scope = null)
    {
        if ($scope == null) {
            $scope = ScopeInterface::SCOPE_STORE;
        }
        return $this->scopeConfig->getValue($path, $scope);
    }
}
