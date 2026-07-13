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

namespace Lof\Formbuilder\Model;

use Lof\Formbuilder\Api\Data\SubmitFormInterface;
use Lof\Formbuilder\Api\Data\FormbuilderMessageInterface;
use Lof\Formbuilder\Api\Data\SubmitFormInterfaceFactory;
use Lof\Formbuilder\Api\Data\FormbuilderMessageInterfaceFactory;
use Lof\Formbuilder\Api\SubmitFormRepositoryInterface;
use Lof\Formbuilder\Api\FormbuilderRepositoryInterface;
use Lof\Formbuilder\Helper\Data;
use Lof\Formbuilder\Helper\Barcode;
use Lof\Formbuilder\Helper\Fields;
use Lof\Formbuilder\Model\ResourceModel\Message as ResourceMessage;
use Lof\Formbuilder\Model\ResourceModel\Form as ResourceForm;
use Lof\Formbuilder\Model\ResourceModel\Message\CollectionFactory as MessageCollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Helper\Context;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode;

class SubmitFormRepository implements SubmitFormRepositoryInterface
{
    /**
     * @var FormbuilderRepositoryInterface
     */
    protected FormbuilderRepositoryInterface $formbuilderRepository;

    /**
     * @var SubmitFormInterfaceFactory
     */
    protected SubmitFormInterfaceFactory $submitFormFactory;

    /**
     * @var BlacklistFactory
     */
    protected BlacklistFactory $blacklistFactory;

    /**
     * @var FormbuilderMessageInterfaceFactory
     */
    protected FormbuilderMessageInterfaceFactory $formMessageFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected CustomerRepositoryInterface $customerRepository;

    /**
     * @var Data
     */
    protected Data $helperData;

    /**
     * @var MessageFactory
     */
    protected MessageFactory $messageFactory;

    /**
     * @var FormFactory
     */
    protected FormFactory $formFactory;

    /**
     * @var ResourceMessage
     */
    protected ResourceMessage $resourceMessage;

    /**
     * @var ResourceForm
     */
    protected ResourceForm $resourceForm;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var RemoteAddress
     */
    private RemoteAddress $remoteAddress;

    /**
     * @var MessageCollectionFactory
     */
    private MessageCollectionFactory $messageCollectionFactory;

    /**
     * @var LayoutInterface
     */
    private LayoutInterface $layout;

    /**
     * @var Barcode
     */
    protected Barcode $barcodeHelper;

    /**
     * @var Fields
     */
    protected Fields $formFieldHelper;

    /**
     * @var StateInterface
     */
    protected StateInterface $inlineTranslation;

    /**
     * @var TransportBuilder
     */
    protected TransportBuilder $transportBuilder;
    protected int $storeId;

    /**
     * SubmitFormRepository constructor.
     * @param FormbuilderRepositoryInterface $formbuilderRepository
     * @param BlacklistFactory $blacklistFactory
     * @param Data $helperData
     * @param Barcode $barcodeHelper
     * @param Fields $formFieldHelper
     * @param FormbuilderMessageInterfaceFactory $formMessageFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param FormFactory $formFactory
     * @param MessageFactory $messageFactory
     * @param ResourceMessage $resourceMessage
     * @param MessageCollectionFactory $messageCollectionFactory
     * @param ResourceForm $resourceForm
     * @param SerializerInterface $serializer
     * @param RemoteAddress $remoteAddress
     * @param LayoutInterface $layout
     * @param StateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        FormbuilderRepositoryInterface $formbuilderRepository,
        BlacklistFactory $blacklistFactory,
        Data $helperData,
        Barcode $barcodeHelper,
        Fields $formFieldHelper,
        FormbuilderMessageInterfaceFactory $formMessageFactory,
        CustomerRepositoryInterface $customerRepository,
        FormFactory $formFactory,
        MessageFactory $messageFactory,
        ResourceMessage $resourceMessage,
        MessageCollectionFactory $messageCollectionFactory,
        ResourceForm $resourceForm,
        SerializerInterface $serializer,
        RemoteAddress $remoteAddress,
        LayoutInterface $layout,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder
    ) {
        $this->formbuilderRepository = $formbuilderRepository;
        $this->blacklistFactory = $blacklistFactory;
        $this->helperData = $helperData;
        $this->formMessageFactory = $formMessageFactory;
        $this->customerRepository = $customerRepository;
        $this->formFactory = $formFactory;
        $this->messageFactory = $messageFactory;
        $this->resourceMessage = $resourceMessage;
        $this->resourceForm = $resourceForm;
        $this->serializer = $serializer;
        $this->remoteAddress = $remoteAddress;
        $this->layout = $layout;
        $this->messageCollectionFactory = $messageCollectionFactory;
        $this->barcodeHelper = $barcodeHelper;
        $this->formFieldHelper = $formFieldHelper;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
    }

    /**
     * @param int $customerId
     * @param SubmitFormInterface $formData
     * @param int|null $storeId
     * @return int
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws AlreadyExistsException
     */
    public function submitForm(int $customerId, SubmitFormInterface $formData, int $storeId = null): int
    {
        if ($storeId) {
            $this->setStoreId($storeId);
        }
        $formId = $formData->getFormId();
        if (!$formId) {
            throw new NoSuchEntityException(__('The form ID is missing.'));
        }
        $fields = $formData->getFields();
        if (!$fields) {
            throw new NoSuchEntityException(__('You should submit form data, at now the submitted data is empty.'));
        }
        /**
         * Check black list ip address or not
         */
        if ($this->verifyBlacklist()) {
            throw new CouldNotSaveException(
                __('Could not submit the form: %1 . Wrong IP', $formId)
            );
        }

        $formModel = $this->formFactory->create();
        $this->resourceForm->load($formModel, $formId);
        $fields = $formModel->getFields();
        $data = $this->formatFormFieldData($formData);

        /**
         * Check google recaptcha is valid
         */
        if ($this->verifyRecaptcha($formModel, $formData->getCaptcha())) {
            throw new CouldNotSaveException(
                __('Could not submit the form: %1 . Please verify captcha!', $formId)
            );
        }

        $data = $this->uploadFormFiles($data, $formModel, $fields);
        if (!$data) {
            throw new CouldNotSaveException(
                __('Could not submit the form: %1, Wrong upload files', $formId)
            );
        }

        $formModel->escapeFormData($data);

        $customformData = $formModel->getCustomFormFields($data);
        $data = $this->generateMessageHtml($customformData, $data);
        $formSubmitData = [];
        if ($customformData) {
            foreach ($customformData as $key => $val) {
                if (isset($formSubmitData[$val['label']])) {
                    $val['label'] .= " " . $key;
                }
                $formSubmitData[$val['label']] = $this->helperData->xssClean($val['value']);
            }
        }

        $checkBlacklistEmail = $this->checkBlacklistEmails($formSubmitData);
        if (!$checkBlacklistEmail) {
            throw new CouldNotSaveException(
                __('Could not submit the form: %1, your email address was blocked', $formId)
            );
        }

        $ipAddress = $this->remoteAddress->getRemoteAddress();
        $params = [
            "brower" => "rest api",
            "http_host" => "REST_API",
            "current_url" => "",
            "submit_data" => $formSubmitData
        ];
        $messageData = [
            "form_id" => $formId,
            "product_id" => (int)$formData->getProductId(),
            "customer_id" => $customerId,
            "subject" => "from api",
            "email_from" => $this->helperData->getSenderEmail(),
            "message" => $data["message_html"] ?? "",
            "ip_address" => $ipAddress,
            "params" => $this->helperData->encodeData($params),
            "form_data" => $this->helperData->encodeData($customformData),
            "creation_time" => $this->helperData->getTimezoneDateTime()
        ];

        /** Save message */
        $message = $this->messageFactory->create();
        $message->setData($messageData);
        $this->resourceMessage->save($message);

        /** send email */
        $data["qrcode"] = $message->getQrcode();
        $data["barcode"] = $this->barcodeHelper->generateBarcodeLabel($message, false);
        $data["qrcode_tracking_link"] = $this->helperData->getQrcodeTracklink($message);
        $data["track_url"] = $this->helperData->getTrackUrl($message);

        /** Update Data Field Value if array will convert to string */
        $field_prefix = $this->formFieldHelper->getFieldPrefix();
        foreach ($data as $data_key => $data_value) {
            if (str_contains($data_key, $field_prefix)) {
                if (is_array($data_value)) {
                    $data[$data_key] = implode(", ", $data_value);
                }
            }
        }

        $sendEmailReturn = $this->sendNotificationEmails($formModel, $data, $fields);
        $sendThanksEmailReturn = $this->sendThanksyouEmails($formModel, $data, $fields);
        $error = $sendThanksEmailReturn["error"] || $sendEmailReturn["error"];
        if ($error) {
            throw new NoSuchEntityException(__('Some error when sending emails.'));
        }
        return (int)$message->getId();
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function verifyBlacklist(): bool
    {
        $flag = false;
        $enableBlacklist = $this->helperData->getConfig('general_settings/enable_blacklist');
        if ($enableBlacklist) {
            $client_ip = $this->remoteAddress->getRemoteAddress();
            $blacklistModel = $this->blacklistFactory->create();
            if ($client_ip) {
                $blacklistModel->loadByIp($client_ip);
                if ((0 < $blacklistModel->getId()) && $blacklistModel->getStatus()) {
                    $flag = true;
                }
            }
        }
        return $flag;
    }

    /**
     * @param $form
     * @param string $captchaResponse
     * @return bool
     * @throws NoSuchEntityException
     */
    public function verifyRecaptcha($form, string $captchaResponse = ""): bool
    {
        $flag = false;
        if ((int)$form->getShowCaptcha()) {
            $ip = $this->remoteAddress->getRemoteAddress();
            if ($captchaResponse && (int)$captchaResponse === 0) {
            } elseif ($captchaResponse && $ip) {
                $secretKey = $this->helperData->getConfig('general_settings/captcha_privatekey');
                $response = @file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" .
                    $secretKey . "&response=" . $captchaResponse . "&remoteip=" . $ip);
                $responseKeys = json_decode($response, true);
                if (intval($responseKeys["success"]) !== 1) {
                    $flag = true;
                }
            }
        }
        return $flag;
    }

    /**
     * Format form field data
     *
     * @param SubmitFormInterface $formData
     * @return array
     */
    public function formatFormFieldData(SubmitFormInterface $formData): array
    {
        $responseData = [];
        $submitFields = $formData->getFields();
        foreach ($submitFields as $_field) {
            $responseData[$_field->getFieldName()] = $_field->getValue();
        }
        return $responseData;
    }

    /**
     * @param $customformData
     * @param $data
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function generateMessageHtml($customformData, $data): mixed
    {
        $creationTime = $this->helperData->getTimezoneDateTime();
        $messageHtml = $this->layout->createBlock(Template::class)
            ->setTemplate("Lof_Formbuilder::email/items.phtml")
            ->setCustomFormData($customformData)
            ->setCreationTime($creationTime)
            ->toHtml();

        $show_all_fields = $this->helperData->getConfig('email_settings/show_all_fields');
        if (!$show_all_fields) {
            $data['message'] = $this->layout->createBlock(Template::class)
                ->setTemplate("Lof_Formbuilder::email/items_check_enable.phtml")
                ->setCustomFormData($customformData)
                ->setCreationTime($creationTime)
                ->toHtml();
        } else {
            $data['message'] = $messageHtml;
        }
        $data['message_html'] = $messageHtml;
        return $data;
    }

    /**
     * @param $data
     * @param $form
     * @param $fields
     * @return false
     * @throws NoSuchEntityException
     */
    public function uploadFormFiles($data, $form, $fields): bool
    {
        $flag = true;
        $mediaUrl = $this->helperData->getBaseMediaUrl();
        $mediaFolder = $this->helperData->getMediaFilePath();
        $fieldPrefix = $this->helperData->getFieldPrefix();
        if (!empty($fields)) {
            foreach ($fields as $key => $field) {
                $cid = $this->helperData->getFieldId($field);
                $fieldName = $fieldPrefix . $cid . $form->getId();
                $fileValue = $data[$fieldName];
                if ($field['field_type'] == 'file_upload' && $fileValue) {
                    $fileValueDecode = base64_decode($fileValue);
                    if ($fileValueDecode) {
                        $imgExtens = ["gif", "jpeg", "jpg", "png"];
                        $temp = explode(".", $fileValueDecode);
                        $extension = end($temp);
                        $fileSize = 0;
                        $data[$fieldName] = $fileValue;
                        $data[$fieldName . '_filename'] = $fieldName;
                        $data[$fieldName . '_fileurl'] = $mediaUrl . $mediaFolder . '/' . $fieldName;
                        $data[$fieldName . '_filesize'] = $fileSize;
                        if (in_array($extension, $imgExtens)) {
                            $data[$fieldName . '_isimage'] = true;
                        }
                    } else {
                        $flag = false;
                        break;
                    }
                }
            }
        }
        return $flag ? $data : false;
    }

    /**
     * @param $form
     * @param $data
     * @param $fields
     * @return bool[]
     * @throws NoSuchEntityException
     */
    public function sendNotificationEmails($form, $data, $fields): array
    {
        $this->inlineTranslation->suspend();

        $flag = true;
        $error = false;
        $storeId = $this->getStoreId();
        $fieldPrefix = $this->helperData->getFieldPrefix();
        $emails = $form->getData('email_receive');
        $currentUrl = $data['current_url'] ?? '';
        $replyEmailArr = [];
        if ($fields) {
            foreach ($fields as $fitem) {
                $fieldType = $fitem['field_type'] ?? "";
                $cid = $this->helperData->getFieldId($fitem);
                $fieldId = $fieldPrefix . $cid . $form->getId();
                if (
                    $fieldType == "email" && isset($data[$fieldId]) &&
                    $data[$fieldId] && $this->helperData->validateEmailAddress($data[$fieldId])
                ) {
                    $replyEmailArr[] = @trim($data[$fieldId]);
                }
            }
        }
        if (@trim($emails) != '') {
            $fromAddress = $this->helperData->getConfig('email_setting/sender_email_identity');
            $fromAddress = $fromAddress === null ? 'general' : $fromAddress;
            $senderEmailField = $form->getData('sender_email_field');
            $senderNameField = $form->getData('sender_name_field');
            if ($senderEmailField) {
                $senderEmailField = $this->formFieldHelper->getFieldPrefix() . $senderEmailField . $form->getId();
                if (
                    isset($data[$senderEmailField]) && $data[$senderEmailField] &&
                    $this->helperData->validateEmailAddress($data[$senderEmailField])
                ) {
                    $fromAddress = ['email' => $data[$senderEmailField]];

                    $senderNameField = $this->formFieldHelper->getFieldPrefix() . $senderNameField . $form->getId();
                    if (isset($data[$senderNameField]) && $data[$senderNameField]) {
                        $fromAddress['name'] = $data[$senderNameField];
                    }

                }
            }

            $emails = explode(',', $emails);

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
                    $transportBuilder = $this->transportBuilder
                        ->setTemplateIdentifier($form->getData('email_template'))
                        ->setTemplateOptions(
                            [
                                'area' => 'frontend',
                                'store' => $storeId
                            ]
                        )
                        ->setTemplateVars(['data' => $postObject])
                        ->setFrom($fromAddress)
                        ->addTo($v);
                    if (is_array($fromAddress) && isset($fromAddress['email']) && $fromAddress['email']) {
                        $transportBuilder->setReplyTo($fromAddress['email']);
                    } else {
                        if ($replyEmailArr) {
                            $transportBuilder->setReplyTo($replyEmailArr[0]);
                        }
                    }

                    $transport = $transportBuilder->getTransport();
                    try {
                        $transport->sendMessage();
                        $this->inlineTranslation->resume();
                    } catch (\Exception $e) {
                        $error = true;
                        $this->helperData->writeLogData($e);
                    }
                } catch (\Exception $e) {
                    $this->inlineTranslation->resume();
                    $this->helperData->writeLogData($e);
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
     * @param $form
     * @param $data
     * @param $fields
     * @return bool[]
     * @throws NoSuchEntityException
     */
    public function sendThanksyouEmails($form, $data, $fields): array
    {
        $this->inlineTranslation->suspend();

        $error = false;
        $flag = true;
        $storeId = $this->getStoreId();
        $fieldPrefix = $this->helperData->getFieldPrefix();
        $field = $form->getData('thankyou_field');
        $sendThanksEmail = $this->helperData->getConfig('email_settings/send_thanks_email');
        $thanksEmailAll = $this->helperData->getConfig('email_settings/thanks_email_all');

        if ($sendThanksEmail && $thanksEmailAll && $fields) {
            foreach ($fields as $fitem) {
                $fieldType = $fitem['field_type'] ?? "";
                $cid = $this->helperData->getFieldId($fitem);
                $fieldId = $fieldPrefix . $cid . $form->getId();
                if (
                    $fieldType == "email" && isset($data[$fieldId]) &&
                    $data[$fieldId] && $this->helperData->validateEmailAddress($data[$fieldId]) &&
                    $form->getData('thankyou_email_template')
                ) {
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
                                    'store' => $storeId
                                ]
                            )
                            ->setTemplateVars(['data' => $postObject])
                            ->setFrom($this->helperData->getConfig('email_settings/sender_email_identity'))
                            ->addTo($data[$fieldId])
                            ->getTransport();
                        try {
                            $transport->sendMessage();
                            $this->inlineTranslation->resume();
                        } catch (\Exception $e) {
                            $error = true;
                            $this->helperData->writeLogData($e);
                        }
                    } catch (\Exception $e) {
                        $this->helperData->writeLogData($e);
                        $this->inlineTranslation->resume();
                        $flag = false;
                        break;
                    }
                }
            }
        } elseif ($field && $sendThanksEmail) {
            $field = $this->formFieldHelper->getFieldPrefix() . $field . $form->getId();
            if (isset($data[$field]) && $data[$field] && $this->helperData->validateEmailAddress($data[$field])) {
                try {
                    $postObject = new DataObject();
                    $data['form_id'] = $form->getId();
                    $data['title'] = $form->getData('title');
                    $data[$field] = @trim($data[$field]);
                    $postObject->setData($data);

                    $transport = $this->transportBuilder
                        ->setTemplateIdentifier($form->getData('thankyou_email_template'))
                        ->setTemplateOptions(
                            [
                                'area' => 'frontend',
                                'store' => $storeId
                            ]
                        )
                        ->setTemplateVars(['data' => $postObject])
                        ->setFrom($this->helperData->getConfig('email_settings/sender_email_identity'))
                        ->addTo($data[$field])
                        ->getTransport();
                    try {
                        $transport->sendMessage();
                        $this->inlineTranslation->resume();
                    } catch (\Exception $e) {
                        $error = true;
                        $this->helperData->writeLogData($e);
                    }
                } catch (\Exception $e) {
                    $this->helperData->writeLogData($e);
                    $this->inlineTranslation->resume();
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
     * @param $formSubmitData
     * @return bool
     * @throws NoSuchEntityException
     */
    public function checkBlacklistEmails($formSubmitData): bool
    {
        $flag = true;
        $enableBlacklist = $this->helperData->getConfig('general_settings/enable_blacklist');
        if ($formSubmitData && $enableBlacklist) {
            $emails = $this->helperData->getEmailsFromData($formSubmitData);
            if ($emails) {
                $blacklistCollection = $this->messageCollectionFactory->create()
                                        ->addFieldToFilter("status", Blacklist::STATUS_ENABLED);
                $blacklistCollection->addEmailsToFilter($emails);
                if ($blacklistCollection->getSize()) {
                    $flag = false;
                }
            }
        }
        return $flag;
    }

    /**
     * Set store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId(int $storeId): static
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId(): int
    {
        if (!$this->storeId) {
            $store = $this->helperData->getStore();
            $this->setStoreId($store->getId());
        }
        return $this->storeId;
    }
}
