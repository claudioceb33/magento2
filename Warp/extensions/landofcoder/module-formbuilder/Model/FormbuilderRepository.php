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

use Exception;
use Lof\Formbuilder\Api\Data\FormbuilderDesignInterface;
use Lof\Formbuilder\Api\Data\FormbuilderInterface;
use Lof\Formbuilder\Api\Data\FormbuilderInterfaceFactory;
use Lof\Formbuilder\Api\Data\FormbuilderMessageInterface;
use Lof\Formbuilder\Api\Data\FieldDesignInterfaceFactory;
use Lof\Formbuilder\Api\Data\FieldOptionInterfaceFactory;
use Lof\Formbuilder\Api\Data\FormbuilderDesignInterfaceFactory;
use Lof\Formbuilder\Api\Data\FormbuilderMessageInterfaceFactory;
use Lof\Formbuilder\Api\Data\FormbuilderMessageSearchResultsInterface;
use Lof\Formbuilder\Api\Data\FormbuilderMessageSearchResultsInterfaceFactory;
use Lof\Formbuilder\Api\Data\FormbuilderSearchResultsInterface;
use Lof\Formbuilder\Api\Data\FormbuilderSearchResultsInterfaceFactory;
use Lof\Formbuilder\Api\FormbuilderRepositoryInterface;
use Lof\Formbuilder\Helper\Data;
use Lof\Formbuilder\Model\ResourceModel\Form\CollectionFactory as FormCollectionFactory;
use Lof\Formbuilder\Model\ResourceModel\Message\CollectionFactory as MessageCollection;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\EntityManager\HydratorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Store\Model\Store;

class FormbuilderRepository implements FormbuilderRepositoryInterface
{
    /**
     * @var FormbuilderInterfaceFactory
     */
    protected FormbuilderInterfaceFactory $formbuilderFactory;

    /**
     * @var FormbuilderMessageInterfaceFactory
     */
    protected FormbuilderMessageInterfaceFactory $formbuildermessageFactory;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $Collection;

    /**
     * @var MessageCollection
     */
    protected MessageCollection $messageCollection;

    /**
     * @var CollectionProcessorInterface
     */
    private CollectionProcessorInterface $collectionProcessor;

    /**
     * @var FormbuilderSearchResultsInterfaceFactory
     */
    protected FormbuilderSearchResultsInterfaceFactory $searchResultsFactory;

    /**
     * @var FormbuilderMessageSearchResultsInterfaceFactory
     */
    protected FormbuilderMessageSearchResultsInterfaceFactory $searchMessageResultsFactory;

    /**
     * @var Request
     */
    protected Request $request;

    /**
     * @var BlacklistFactory
     */
    protected BlacklistFactory $blacklist;

    /**
     * @var Form
     */
    protected Form $form;

    /**
     * @var ResourceConnection
     */
    protected ResourceConnection $resource;

    /**
     * @var Data
     */
    protected Data $helperData;

    /**
     * @var ReplyFactory
     */
    protected ReplyFactory $replyFactory;

    /**
     * @var JsonFactory
     */
    protected JsonFactory $resultJsonFactory;

    /**
     * @var StateInterface
     */
    protected StateInterface $inlineTranslation;

    /**
     * @var DataPersistorInterface
     */
    private TransportBuilder|DataPersistorInterface $transportBuilder;
    /**
     * @var Context
     */
    private Context $context;

    /**
     * @var FormFactory
     */
    private FormFactory $formFactory;

    /**
     * @var FormCollectionFactory
     */
    private FormCollectionFactory $formCollectionFactory;

    /**
     * @var JoinProcessorInterface
     */
    private JoinProcessorInterface $extensionAttributesJoinProcessor;
    /**
     * @var HydratorInterface
     */
    private HydratorInterface $hydrator;

    /**
     * @var MessageFactory
     */
    private MessageFactory $messageFactory;

    /**
     * @var FieldDesignInterfaceFactory
     */
    private FieldDesignInterfaceFactory $fieldDesignFactory;

    /**
     * @var FormbuilderDesignInterfaceFactory
     */
    private FormbuilderDesignInterfaceFactory $formDesignFactory;

    /**
     * @var FieldOptionInterfaceFactory
     */
    private FieldOptionInterfaceFactory $fieldOptionFactory;
    /**
     * @var string
     */
    protected string $prefixSubject;

    /**
     * @param FormbuilderInterfaceFactory $formbuilderFactory
     * @param FormbuilderMessageInterfaceFactory $formbuildermessageFactory
     * @param FormCollectionFactory $formCollectionFactory
     * @param MessageCollection $messageCollection
     * @param CollectionProcessorInterface $collectionProcessor
     * @param FormbuilderSearchResultsInterfaceFactory $searchResultsFactory
     * @param FormbuilderMessageSearchResultsInterfaceFactory $searchMessageResultsFactory
     * @param Request $request
     * @param BlacklistFactory $blacklist
     * @param FormFactory $formFactory
     * @param Form $form
     * @param MessageFactory $messageFactory
     * @param ResourceConnection $resource
     * @param Data $helper
     * @param Context $context
     * @param TransportBuilder $transportBuilder
     * @param JsonFactory $resultJsonFactory
     * @param StateInterface $inlineTranslation
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ReplyFactory $replyFactory
     * @param FieldDesignInterfaceFactory $fieldDesignFactory
     * @param FormbuilderDesignInterfaceFactory $formDesignFactory
     * @param FieldOptionInterfaceFactory $fieldOptionFactory
     * @param HydratorInterface|null $hydrator
     */
    public function __construct(
        FormbuilderInterfaceFactory $formbuilderFactory,
        FormbuilderMessageInterfaceFactory $formbuildermessageFactory,
        FormCollectionFactory $formCollectionFactory,
        MessageCollection $messageCollection,
        CollectionProcessorInterface $collectionProcessor,
        FormbuilderSearchResultsInterfaceFactory $searchResultsFactory,
        FormbuilderMessageSearchResultsInterfaceFactory $searchMessageResultsFactory,
        Request $request,
        BlacklistFactory $blacklist,
        FormFactory $formFactory,
        Form $form,
        MessageFactory $messageFactory,
        ResourceConnection $resource,
        Data $helper,
        Context $context,
        TransportBuilder $transportBuilder,
        JsonFactory $resultJsonFactory,
        StateInterface $inlineTranslation,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ReplyFactory $replyFactory,
        FieldDesignInterfaceFactory $fieldDesignFactory,
        FormbuilderDesignInterfaceFactory $formDesignFactory,
        FieldOptionInterfaceFactory $fieldOptionFactory,
        ?HydratorInterface $hydrator = null
    ) {
        $this->formbuilderFactory = $formbuilderFactory;
        $this->formbuildermessageFactory = $formbuildermessageFactory;
        $this->formCollectionFactory = $formCollectionFactory;
        $this->messageCollection = $messageCollection;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->searchMessageResultsFactory = $searchMessageResultsFactory;
        $this->request = $request;
        $this->blacklist = $blacklist;
        $this->formFactory = $formFactory;
        $this->form = $form;
        $this->messageFactory = $messageFactory;
        $this->resource = $resource;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->inlineTranslation = $inlineTranslation;
        $this->helperData = $helper;
        $this->transportBuilder = $transportBuilder;
        $this->hydrator = $hydrator;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->context = $context;
        $this->replyFactory = $replyFactory;
        $this->fieldDesignFactory = $fieldDesignFactory;
        $this->formDesignFactory = $formDesignFactory;
        $this->fieldOptionFactory = $fieldOptionFactory;
    }

    /**
     * @inheritdoc
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria,
        int $customerGroupId = null,
        int $store = null
    ): FormbuilderSearchResultsInterface {
        $collection = $this->formCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $items = [];
        $total = $collection->getSize();
        foreach ($collection as $key => $model) {
            if ($customerGroupId != null) {
                $formCustomerGroups = $model->getData('customergroups');
                if (!in_array($customerGroupId, $formCustomerGroups)) {
                    $total--;
                    continue;
                }

            }
            if ($store != null) {
                $formStores = $model->getStores();
                $formStores = !is_array($formStores) ? [(int)$formStores] : $formStores;
                if (!in_array(0, $formStores) && !in_array((int)$store, $formStores)) {
                    $total--;
                    continue;
                }
            }
            $items[] = $model;
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($total);
        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function getFormById(int $formId): FormbuilderInterface
    {
        $form = $this->formFactory->create();
        $form->load($formId);
        if (!$form->getId()) {
            throw new NoSuchEntityException(__('The Form with the "%1" ID doesn\'t exist.', $formId));
        }

        return $form;
    }

    /**
     * @inheritdoc
     */
    public function save(FormbuilderInterface $form): FormbuilderInterface
    {
        $formId = $form->getId();

        try {
            if ($formId) {
                $form = $this->hydrator->hydrate($this->getFormById($formId), $this->hydrator->extract($form));
            }
            $this->formFactory->create()->setData($form)->save();
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the form: %1', $exception->getMessage()),
                $exception
            );
        }
        return $form;
    }

    /**
     * @inheritdoc
     */
    public function deleteById(int $formId): bool
    {
        try {
            $formModel = $this->formbuilderFactory->create();

            $formModel->load($formId);

            if (!$formModel->getId()) {
                throw new NoSuchEntityException(__('Form with id "%1" does not exist.', $formId));
            }

            $formModel->delete();
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__('Could not delete the form: %1', $exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getMessageById(int $messageId): FormbuilderMessageInterface
    {
        $message = $this->messageFactory->create();
        $message->load($messageId);
        if (!$message->getId()) {
            throw new NoSuchEntityException(__('The Form with the "%1" ID doesn\'t exist.', $messageId));
        }

        return $message;
    }

    /**
     * @inheritdoc
     */
    public function getListMessage(SearchCriteriaInterface $criteria): FormbuilderMessageSearchResultsInterface
    {
        $collection = $this->messageCollection->create();
        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $items = [];
        foreach ($collection as $key => $model) {
            $items[$key] = $model;
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function getLastMessage(SearchCriteriaInterface $criteria): FormbuilderMessageSearchResultsInterface
    {
        $collection = $this->messageCollection->create()->setOrder('message_id', 'DESC');
        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $items = [];
        foreach ($collection as $key => $model) {
            $items[$key] = $model;
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function deleteMessageById(int $messageId): bool
    {
        try {
            $messageModel = $this->formbuildermessageFactory->create();

            $messageModel->load($messageId);

            if (!$messageModel->getId()) {
                throw new NoSuchEntityException(__('Message with id "%1" does not exist.', $messageId));
            }

            $messageModel->delete();
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__('Could not delete the message: %1', $exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getFormFieldData(int $customerId, int $messageId): mixed
    {
        $result = [];
        $collection = $this->messageCollection->create();
        $collection->addFieldToFilter('message_id', $messageId)
                    ->addFieldToFilter('customer_id', $customerId);

        if ($collection->getSize()) {
            foreach ($collection->getItems() as $formdata) {
                $result[] = json_decode($formdata->getFormData());
            }
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getMessageContent(int $customerId, int $limit = 10, int $page = 1): mixed
    {
        $result = [];
        $collection = $this->messageCollection->create()
                            ->addFieldToFilter("customer_id", $customerId);

        foreach ($collection->getItems() as $message) {
            $result[]['message'] = $message["message"];
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getMyMessageById(int $customerId, int $messageId): FormbuilderMessageInterface
    {
        $message = $this->messageFactory->create();
        $message->load($messageId);
        if (!$message->getId() || (int)$message->getCustomerId() != (int)$customerId) {
            throw new NoSuchEntityException(__('The form message with the "%1" ID doesn\'t exist.', $messageId));
        }

        return $message;
    }

    /**
     * @inheritdoc
     */
    public function getMyListMessage(
        int $customerId,
        SearchCriteriaInterface $criteria
    ): FormbuilderMessageSearchResultsInterface {
        $collection = $this->messageCollection->create();
        $this->collectionProcessor->process($criteria, $collection);

        $collection->addFieldToFilter("customer_id", $customerId);

        $items = [];

        foreach ($collection as $key => $model) {
            $items[] = $model;
        }

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function getAvailableFormById(
        int $formId,
        int $customerGroupId = null,
        int $store = null
    ): FormbuilderInterface {
        $form = $this->getActiveFormProfile($formId, $customerGroupId, $store);
        $form->setDesign("");

        $formFieldsArray = $this->getFormField($formId, $customerGroupId, $store);
        $designFields = $this->formatFormFields($formFieldsArray, $formId);

        $form->setDesignFields($designFields);

        return $form;
    }

    /**
     * @inheritdoc
     */
    public function getFormField(int $formId, int $customerGroupId = null, int $storeId = null): mixed
    {
        $result = [];
        $connection = $this->resource->getConnection();
        $table1 = $connection->getTableName('lof_formbuilder_form_customergroup');
        $table2 = $connection->getTableName('lof_formbuilder_form_store');
        $collection = $this->formFactory->create();
        if ($storeId) {
            $stores = [0, $storeId];
        } else {
            $stores = [0];
        }

        $where = "";

        if ($customerGroupId) {
            $where = ' and ' . $table1 . '.customer_group_id =' . $customerGroupId;
        }

        $sql = $collection->getCollection()->getSelect('e.design')
            ->joinLeft($table1, 'main_table.form_id =' . $table1 . '.form_id')
            ->joinLeft($table2, 'main_table.form_id =' . $table2 . '.form_id')
            ->where('main_table.form_id =' . $formId . $where . ' and ' . $table2 . '.store_id in (?)', $stores);

        $formField = $this->resource->getConnection()->fetchAll($sql);

        if ($formField) {
            foreach ($formField as $formfield) {
                $result[]['formfield'] = $formfield["design"];
            }
        } else {
            throw new CouldNotDeleteException(__('Form design is not valid'));
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getFormDesign(
        int $formId,
        int $customerGroupId = null,
        int $storeId = null
    ): FormbuilderDesignInterface {
        $formData = $this->getActiveFormProfile($formId, $customerGroupId, $storeId);
        $formData->setDesign("");

        $formFieldsArray = $this->getFormField($formId, $customerGroupId, $storeId);
        $designFields = $this->formatFormFields($formFieldsArray, $formId);

        $formDesigbObject = $this->formDesignFactory->create();
        $formDesigbObject->setFields($designFields);
        $formDesigbObject->setForm($formData);
        return $formDesigbObject;
    }

    /**
     * @return bool
     * @throws CouldNotSaveException
     */
    public function addIp(): bool
    {
        $ip[] = $this->request->getBodyParams();
        $ip = $ip[0]['ip'];
        $blackList = $this->blacklist->create();
        try {
            $blackList
                ->setIp($ip);
            $blackList->save();
        } catch (Exception $e) {
            throw new CouldNotSaveException(__('Could not save the Ip: %1', $e->getMessage()));
        }
    }

    /**
     * @return bool
     * @throws CouldNotSaveException
     */
    public function send(): bool
    {
        // check if data sent
        $data[] = $this->request->getBodyParams();
        if ($data) {
            $model = $this->replyFactory->create();
            $emailFrom = $data[0]['email_from'];
            $emailTo = $data[0]['email_to'];
            $formId = $data[0]['form_id'];
            $messageId = $data[0]['message_id'];
            $message = $data[0]['message'];
            $subject = $data[0]['subject'];

            if ($emailFrom) {
                $model->loadByEmailFrom($emailFrom);
            }

            if ($emailTo) {
                $model->loadByEmailTo($emailTo);
            }
            $data[0]['subject'] = strip_tags($data[0]['subject']);
            $data[0]['subject'] = @trim($data[0]['subject']);
            $variables = [
                "subject" => $data[0]['subject'],
                "message" => $data[0]['message']
            ];
            $this->sendEmail($data, $emailFrom, $emailTo, $variables);
            try {
                $model->setSubject($subject)
                    ->setEmailFrom($emailFrom)
                    ->setEmailTo($emailTo)
                    ->setMessage($message);
                $model->save();
            } catch (\Exception $e) {
                throw new CouldNotSaveException(__(
                    'Have problem when save the reply message. %1',
                    $e->getMessage()
                ));
            }
        }

        return true;
    }

    /**
     * @param $emailFrom
     * @param $data
     * @param $emailTo
     * @param $variables
     */
    public function sendEmail($emailFrom, $data, $emailTo, $variables)
    {
        $data = [];
        $this->inlineTranslation->suspend();
        try {
            $this->transportBuilder
                ->setTemplateOptions([
                    'area' => Area::AREA_FRONTEND,
                    'store' => Store::DEFAULT_STORE_ID,
                ]);

            $this->transportBuilder->setTemplateVars($variables);
            $this->transportBuilder->setTemplateData($data);
            $this->transportBuilder->setFrom($emailFrom);

            $this->transportBuilder->addTo($emailTo);

            $this->prefixSubject = '';

            $transport = $this->transportBuilder->getTransport();

            $transport->sendMessage();

            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
        }
    }

    /**
     * @param array $formFieldsArray
     * @param int $formId
     * @return array
     */
    public function formatFormFields(array $formFieldsArray = [], int $formId = 0): array
    {
        $designFields = [];
        if ($formFieldsArray) {
            $formFields = $formFieldsArray[0];
            $fields = isset($formFields["formfield"]) &&
            $formFields["formfield"] ? json_decode($formFields["formfield"], true) : [];
            foreach ($fields as $formfield) {
                if ($formfield) {
                    $dataObject = $this->fieldDesignFactory->create();
                    $cid = $this->helperData->getFieldId($formfield);
                    $fieldId = $this->helperData->getFieldPrefix() . $cid . $formId;

                    $dataObject->setLabel($formfield["label"] ?? "");
                    $dataObject->setFieldType($formfield["field_type"] ?? "");
                    $dataObject->setRequired($formfield["required"] ?? "");
                    $dataObject->setFieldOptions(isset($formfield["field_options"]) ?
                        json_encode($formfield["field_options"]) : "");
                    $dataObject->setFieldcol($formfield["fieldcol"] ?? "");
                    $dataObject->setWrappercol($formfield["wrappercol"] ?? "");
                    $dataObject->setCid($cid);
                    $dataObject->setFieldId($fieldId);
                    $dataObject->setInlineCss($formfield["inline_css"] ?? "");

                    $fieldOptions = $formfield["field_options"] ?? [];

                    if ($fieldOptions) {
                        $dataObject->setFieldSize($fieldOptions["size"] ?? "");
                        $dataObject->setFontWeight($fieldOptions["font_weight"] ?? "");
                        $dataObject->setColorText($fieldOptions["color_text"] ?? "");
                        $dataObject->setColorLabel($fieldOptions["color_label"] ?? "");
                        $dataObject->setValidation($fieldOptions["validation"] ?? "");
                        $dataObject->setIncludeBlankOption($fieldOptions["include_blank_option"] ?? "");
                        $dataObject->setFontSize($fieldOptions["font_size"] ?? "");
                        $options = $fieldOptions["options"] ?? [];

                        if ($options) {
                            $optionsArray = [];
                            foreach ($options as $option) {
                                $fieldOption = $this->fieldOptionFactory->create();
                                $fieldOption->setLabel($option["label"] ?? "");
                                $fieldOption->setChecked(isset($option["checked"]) ? $option["label"] : "");
                                $optionsArray[] = $fieldOption;
                            }
                            $dataObject->setOptions($optionsArray);
                        }
                    }
                    $designFields[] = $dataObject;
                }
            }
        }
        return $designFields;
    }

    /**
     * @param int $formId
     * @param $customerGroupId
     * @param $store
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getActiveFormProfile(int $formId, $customerGroupId = null, $store = null): mixed
    {
        $form = $this->formFactory->create();
        $form->load($formId);
        if (!$form->getId()) {
            throw new NoSuchEntityException(__('The Form with the "%1" ID doesn\'t exist.', $formId));
        }
        if ($customerGroupId != null) {
            $formCustomerGroups = $form->getData('customergroups');
            if (!in_array($customerGroupId, $formCustomerGroups)) {
                throw new NoSuchEntityException(
                    __('The Form with the "%1" ID doesn\'t exist for customer group %2.', $formId, $customerGroupId)
                );
            }

        }
        if ($store != null) {
            $formStores = $form->getStores();
            if (!in_array(0, $formStores) && !in_array((int)$store, $formStores)) {
                throw new NoSuchEntityException(
                    __('The Form with the "%1" ID doesn\'t exist for store %2.', $formId, $store)
                );
            }
        }

        $form->setEmailReceive("");
        $form->setThanksEmailTemplate("");
        $form->setEmailTemplate("");
        $form->setSenderEmailField("");
        $form->setSenderNameField("");

        return $form;
    }
}
