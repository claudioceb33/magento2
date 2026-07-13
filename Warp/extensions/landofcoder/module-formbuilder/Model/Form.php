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
use Lof\Formbuilder\Api\Data\FormbuilderInterface;
use Lof\Formbuilder\Helper\Data;
use Lof\Formbuilder\Model\ResourceModel\Form\Collection;
use Magento\Catalog\Helper\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Directory\Model\Country;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\StoreManagerInterface;

class Form extends AbstractModel implements FormbuilderInterface
{
    public const CACHE_BLOCK_TAG = 'lof_formbuilder_block';
    public const CACHE_PAGE_TAG = 'lof_formbuilder_page';
    public const CACHE_MEDIA_TAG = 'lof_formbuilder_media';
    public const CACHE_TAG = 'formbuilder_form';

    /**#@+
     * Form's Statuses
     */
    public const STATUS_ENABLED = 1;
    public const STATUS_DISABLED = 0;
    public const STATUS_DISALLOWED = 2;

    /**
     * @var string
     */
    protected $_cacheTag = 'formbuilder_form';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'formbuilder_form';

    /**
     * Name of the event object
     *
     * @var string
     */
    protected $_eventObject = 'form';

    /**
     * Product collection factory
     *
     * @var CollectionFactory
     */
    protected CollectionFactory $productCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * URL Model instance
     *
     * @var UrlInterface
     */
    protected UrlInterface $url;

    /**
     * @var ResourceModel\Form|null
     */
    protected $resource;

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var
     */
    protected $store;

    /**
     * @var ProductFactory
     */
    protected ProductFactory $productloader;
    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected \Magento\Backend\Helper\Data $helperBackend;
    /**
     * @var Data
     */
    protected Data $helper;
    /**
     * @var SubscriberFactory
     */
    protected SubscriberFactory $subscriber;
    /**
     * @var Country
     */
    protected Country $country;
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected \Magento\Framework\Pricing\Helper\Data $currency;
    /**
     * @var FilterManager
     */
    protected FilterManager $filterManager;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ResourceModel\Form $resource
     * @param StoreManagerInterface $storeManager
     * @param Collection $resourceCollection
     * @param UrlInterface $url
     * @param ScopeConfigInterface $scopeConfig
     * @param Data $helper
     * @param SubscriberFactory $subscriber
     * @param Country $country
     * @param \Magento\Framework\Pricing\Helper\Data $currency
     * @param FilterManager $filterManager
     * @param \Magento\Backend\Helper\Data $helperBackend
     * @param ProductFactory $productloader
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ResourceModel\Form $resource,
        StoreManagerInterface $storeManager,
        Collection $resourceCollection,
        UrlInterface $url,
        ScopeConfigInterface $scopeConfig,
        Data $helper,
        SubscriberFactory $subscriber,
        Country $country,
        \Magento\Framework\Pricing\Helper\Data $currency,
        FilterManager $filterManager,
        \Magento\Backend\Helper\Data $helperBackend,
        ProductFactory $productloader,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->url = $url;
        $this->helper = $helper;
        $this->subscriber = $subscriber;
        $this->country = $country;
        $this->currency = $currency;
        $this->helperBackend = $helperBackend;
        $this->productloader = $productloader;
        $this->filterManager = $filterManager;
    }

    /**
     * Prevent blocks recursion
     *
     * @return AbstractModel
     * @throws LocalizedException
     */
    public function beforeSave(): AbstractModel
    {
        $needle = 'form_id="' . $this->getId() . '"';
        $content = $this->getContent();
        if (!$content || !strstr($content, $needle)) {
            return parent::beforeSave();
        }
        throw new LocalizedException(
            __('Make sure that static form content does not reference the form itself.')
        );
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getIdentifier()];
    }

    /**
     * Receive page store ids
     *
     * @return int[]|string|int
     */
    public function getStores(): array|string|int
    {
        return $this->hasData('stores') ? $this->getData('stores') : $this->getData('store_id');
    }

    /**
     * Prepare page's statuses.
     * Available event cms_page_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses(): array
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Prepare page's statuses.
     * Available event cms_page_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getYesno(): array
    {
        return [self::STATUS_ENABLED => __('Yes'), self::STATUS_DISABLED => __('No')];
    }

    /**
     * @return array
     */
    public function getEnabledDisabled(): array
    {
        return [
            self::STATUS_ENABLED => __('Enabled'),
            self::STATUS_DISALLOWED => __('Disabled'),
            self::STATUS_DISABLED => __('Use Default Config')
        ];
    }

    /**
     * @param $identifier
     * @param $storeId
     * @return int
     * @throws LocalizedException
     */
    public function checkIdentifier($identifier, $storeId): int
    {
        return $this->_getResource()->checkIdentifier($identifier, $storeId);
    }

    /**
     * @param $identifier
     * @param $customerGroupId
     * @return mixed
     * @throws LocalizedException
     */
    public function checkCustomerGroup($identifier, $customerGroupId): mixed
    {
        return $this->_getResource()->checkCustomerGroup($identifier, $customerGroupId);
    }

    /**
     * @param array $postData
     * @return bool
     */
    public function checkEmailInSubscription(array $postData = []): bool
    {
        return true;
    }

    /**
     * @param array $emails
     * @return bool|int
     */
    public function subscriptionListEmails(array $emails = []): bool|int
    {
        $this->_eventManager->dispatch(
            'formbuilder_substription_emails',
            ['model' => $this, 'emails' => $emails]
        );
        $status = false;
        if ($emails) {
            foreach ($emails as $email) {
                $fresh_model = $this->subscriber->create()->setId(null);
                $status = $fresh_model->subscribe($email);
            }
        }
        return $status;
    }

    /**
     * @param array $postData
     * @return bool
     * @throws Exception
     */
    public function escapeFormData(array $postData = []): bool
    {
        $valid = false;
        if ((0 < $this->getId()) && $postData) {
            if ($custom_fields = $this->getFields()) {
                $fieldPrefix = $this->helper->getFieldPrefix();
                foreach ($custom_fields as $i => $field) {
                    $cid = $this->helper->getFieldId($field);
                    $fieldId = $fieldPrefix . $cid . $this->getId();
                    $fieldType = $field['field_type'];
                    $tmp = $field;
                    $tmp['field_cid'] = $fieldId;
                    $fieldValue = $postData[$fieldId] ?? '';
                    $fieldValue = $this->helper->xssClean($fieldValue);

                    switch ($fieldType) {
                        case 'email':
                            if ($fieldValue) {
                                $fieldValue = @trim($fieldValue);
                                if (!$this->helper->validateEmailAddress($fieldValue)) {
                                    throw new Exception(__('Invalid email!'));
                                }

                                $valid = true;
                            }
                            break;
                        default:
                            if ($fieldValue) {
                                $valid = true;
                            }
                            break;
                    }
                }
            }
            return $valid;
        }
        return false;
    }

    /**
     * @param array $postData
     * @return array|false
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function getCustomFormFields(array $postData = []): bool|array
    {
        $subscription_all = $this->helper->getConfig("email_settings/subscription_all");
        if ((0 < $this->getId()) && $postData) {
            $formData = [];
            $emails = [];
            $fullPhones = [];
            $is_subscription = false;
            if ($custom_fields = $this->getFields()) {
                $fieldPrefix = $this->helper->getFieldPrefix();
                foreach ($custom_fields as $i => $field) {
                    $cid = $this->helper->getFieldId($field);
                    $fieldId = $fieldPrefix . $cid . $this->getId();
                    $fieldType = $field['field_type'];

                    //if (isset($postData[$fieldId]) || isset($postData[$fieldId."0"])) {
                    $tmp = $field;
                    $tmpPhone = [];
                    $tmp['field_cid'] = $fieldId;
                    $fieldValue = $postData[$fieldId] ?? '';
                    $fieldValue = $this->helper->xssClean($fieldValue);
                    switch ($fieldType) {
                        case 'website':
                            if ($fieldValue) {
                                if (
                                    (!str_contains($fieldValue, "http://")) &&
                                    (!str_contains($fieldValue, "https://"))
                                ) {
                                    $fieldValue = "http://" . $fieldValue;
                                }
                                $fieldValue = '<a href="' . $fieldValue .
                                    '" target="_BLANK">' . $fieldValue . '</a>';
                            }
                            break;
                        case 'email':
                            if ($fieldValue) {
                                $objectManager = ObjectManager::getInstance();
                                $escaper = $objectManager->create('Magento\Framework\Escaper');
                                $tempEmailAttr = @trim($escaper->escapeHtmlAttr($fieldValue));
                                $tempEmailHtml = @trim($escaper->escapeHtml($fieldValue));
                                $emails[] = $tempEmailAttr;
                                $tmp['thanks_email'] = $tempEmailHtml;
                                $fieldValue = '<a href="mailto:' .
                                    $tempEmailAttr . '" target="_BLANK">' . $tempEmailHtml . '</a>';
                            }
                            break;
                        case 'phone':
                            $telephone_input = $postData[$fieldId . "_telephone_input"] ?? "";
                            $telephone_number = $postData[$fieldId . "_telephone_number"] ?? "";
                            $countryCode = $postData[$fieldId . "_country_code"] ?? "";
                            $fieldValue = $fieldValue ?: @trim($telephone_input);
                            if ($fieldValue) {
                                $objectManager = ObjectManager::getInstance();
                                $escaper = $objectManager->create('Magento\Framework\Escaper');
                                $tempPhoneAttr = @trim($escaper->escapeHtmlAttr($fieldValue));
                                $tempPhonelHtml = @trim($escaper->escapeHtml($fieldValue));
                                $countryCode = @trim($escaper->escapeHtml($countryCode));

                                $tmpPhone = [
                                    "telephone" => $telephone_input,
                                    "telephone_number" => $telephone_number,
                                    "country_code" => $countryCode
                                ];
                                $fullPhones[ $tempPhoneAttr ] = $tmpPhone;


                                if (!strpos($countryCode, "+") || str_contains($tempPhoneAttr, "+")) {
                                    $countryCode = "";
                                }
                                $tmp['thanks_phone'] = $tempPhonelHtml;
                                $fieldValue = '<a href="tel:' . $countryCode . $tempPhoneAttr .
                                    '" target="_BLANK">' . $countryCode . $tempPhonelHtml . '</a>';
                            }
                            break;
                        case 'radio':
                            if ($fieldValue) {
                                if (
                                    $fieldValue == "other" && isset($postData[$fieldId .
                                        "_other"]) && $postData[$fieldId . "_other"]
                                ) {
                                    $fieldValue = $postData[$fieldId . "_other"];
                                }
                                if (str_contains($fieldValue, "{{")) {
                                    $fieldValue = str_replace(
                                        ["{{", "}}"],
                                        ['<img src="{{', '}}" alt="img"/>'],
                                        $fieldValue
                                    );
                                    $fieldValue = $this->helper->filter($fieldValue);
                                } else {
                                    $fieldValue = __($fieldValue);
                                }
                            }
                            break;
                        case 'checkboxes':
                            if (is_array($fieldValue) && $fieldValue) {
                                foreach ($fieldValue as $j => $value) {
                                    if (
                                        $value == "other" && isset($postData[$fieldId .
                                            "_other"]) && $postData[$fieldId . "_other"]
                                    ) {
                                        $fieldValue[$j] = $postData[$fieldId . "_other"];
                                    }

                                    if (str_contains($fieldValue[$j], "{{")) {
                                        $fieldValue[$j] = str_replace(
                                            ["{{", "}}"],
                                            ['<img src="{{', '}}" alt="img"/>'],
                                            $fieldValue[$j]
                                        );
                                        $fieldValue[$j] = $this->helper->filter($fieldValue[$j]);
                                    } else {
                                        $fieldValue[$j] = __($fieldValue[$j]);
                                    }
                                }
                            }
                            if (is_array($fieldValue)) {
                                $fieldValue = implode(", ", $fieldValue);
                            }
                            break;
                        case 'address':
                            $street = $postData[$fieldId . "_street"] ?? "";
                            $street2 = $postData[$fieldId . "_street2"] ?? "";
                            $city = $postData[$fieldId . "_city"] ?? "";
                            $state = $postData[$fieldId . "_state"] ?? "";
                            $zipcode = $postData[$fieldId . "_zipcode"] ?? "";
                            $country = $postData[$fieldId . "_country"] ?? "";
                            $fieldValue = $this->formatAddress($street, $city, $state, $zipcode, $country, $street2);
                            break;
                        case 'file_upload':
                            if (isset($postData[$fieldId . "_fileurl"])) {
                                $fieldValue = '<a href="' . $postData[$fieldId . "_fileurl"] . '" target="_BLANK">';
                                if (isset($postData[$fieldId . "_isimage"])) {
                                    $fieldValue .= '<div><img style="width: 150px" src="' .
                                        $postData[$fieldId . "_fileurl"] . '"/></div>';
                                }
                                $fieldValue .= $postData[$fieldId . "_filename"] . ' - (' . round(
                                    $postData[$fieldId . "_filesize"],
                                    2
                                ) . 'Kb)';
                                $fieldValue .= '</a>';
                            }
                            break;
                        case 'multifile_upload':
                            if ($fieldValue && is_array($fieldValue)) {
                                if (
                                    isset($postData[$fieldId . "_fileurl"]) &&
                                    is_array($postData[$fieldId . "_fileurl"])
                                ) {
                                    $tmp_files = [];
                                    foreach ($postData[$fieldId . "_fileurl"] as $j => $value) {
                                        $tmpFieldValue = '<a href="' . $value . '" target="_BLANK">';
                                        if (
                                            isset($postData[$fieldId . "_isimage"])
                                            && isset($postData[$fieldId . "_isimage"][$j])
                                            && $postData[$fieldId . "_isimage"][$j]
                                        ) {
                                            $tmpFieldValue .= '<div><img style="width: 150px" src="' .
                                                $value . '"/></div>';
                                        }
                                        $tmpFieldValue .= $fieldValue[$j];

                                        if (
                                            isset($postData[$fieldId . "_filesize"]) &&
                                            isset($postData[$fieldId . "_filesize"][$j])
                                        ) {
                                            $tmpFieldValue .= ' - (' . round(
                                                $postData[$fieldId . "_filesize"][$j],
                                                2
                                            ) . 'Kb)';
                                        }

                                        $tmpFieldValue .= '</a>';
                                        $tmp_files[] = $tmpFieldValue;
                                    }
                                    $fieldValue = implode("<br/>", $tmp_files);
                                } else {
                                    $fieldValue = implode(", ", $fieldValue);
                                }
                            }
                            break;
                        case 'model_dropdown':
                            if ($fieldValue && is_array($fieldValue)) {
                                $tmp_models = [];
                                $k = 1;
                                foreach ($fieldValue as $fitem) {
                                    $tmp2 = [];
                                    if (is_array($fitem)) {
                                        foreach ($fitem as $fitem2) {
                                            $tmp2[] = $fitem2;
                                        }
                                    } else {
                                        $tmp2 = [$fitem];
                                    }
                                    if ($tmp2 && $fitem) {
                                        $tmp_models[] = $k . ". " . implode(" > ", $tmp2);
                                    }

                                    $k++;
                                }
                                $fieldValue = implode("<br/>", $tmp_models);
                            }
                            break;
                        case 'price':
                            $fieldValue = $this->currency->currency($fieldValue, true, false);
                            break;
                        case 'time':
                            $hours = $postData[$fieldId . "_hours"] ?? "00";
                            $minutes = $postData[$fieldId . "_minutes"] ?? "00";
                            $seconds = $postData[$fieldId . "_seconds"] ?? "00";
                            $am_pm = $postData[$fieldId . "_am_pm"] ?? "";
                            $fieldValue = $hours . ':' . $minutes . ':' . $seconds . ' ' . $am_pm;
                            break;
                        case 'google_map':
                            $location = $fieldValue;
                            $lat = $postData[$fieldId . "_lat"] ?? "";
                            $long = $postData[$fieldId . "_long"] ?? "";
                            $street = $postData[$fieldId . "_street"] ?? "";
                            $streetNumber = $postData[$fieldId . "_streetNumber"] ?? "";
                            $street1 = $postData[$fieldId . "_addressLine1"] ?? "";
                            $street2 = $postData[$fieldId . "_addressLine2"] ?? "";
                            $street = $street ? $street : $street1;
                            $district = $postData[$fieldId . "_district"] ?? "";
                            $city = $postData[$fieldId . "_city"] ?? "";
                            $state = $postData[$fieldId . "_state"] ?? "";
                            $stateOrProvince = $postData[$fieldId . "_stateOrProvince"] ?? "";
                            $state = $state ? $state : $stateOrProvince;
                            $country = $postData[$fieldId . "_country"] ?? "";
                            $zipcode = $postData[$fieldId . "_postcode"] ?? "";

                            $rand = $postData[$fieldId . "_radius"] ?? "";
                            $fieldValue = $location . "<br/>" . __("Latitude: %1", $lat) . " , " . __(
                                "Longtitude: %1",
                                $long
                            );
                            $showAddress_in_map = $this->helper->getConfig("googleapi/show_address_in_map");
                            if ($showAddress_in_map) {
                                $fieldValue .= "<br/>" . $this->formatAddress(
                                    $street,
                                    $city,
                                    $state,
                                    $zipcode,
                                    $country,
                                    $street2
                                );
                            }
                            break;
                        case 'subscription':
                            $fieldValue = $postData[$fieldId . '0'] ?? "";

                            if (is_array($fieldValue) && $fieldValue) {
                                $fieldValue = $fieldValue[0];
                            }
                            if ($fieldValue == 1) {
                                $is_subscription = true;
                            }

                            $fieldValue = "";
                            $tmp['subscription'] = true;
                            if ($is_subscription) {
                                $fieldValue = __("Yes");
                            } else {
                                $fieldValue = __("No");
                            }

                            break;
                        case 'rating':
                            $limit = isset($postData[$fieldId . "_limit"]) ? (int)$postData[$fieldId . "_limit"] : 5;
                            $rating_value = (float)$fieldValue;
                            if ($limit) {
                                $fieldValue = '<div class="rating small">';
                                for ($i = 1; $i <= $limit; $i++) {
                                    $fclass = "";
                                    if ($i <= $rating_value) {
                                        $fclass = 'on';
                                    }
                                    $fieldValue .= '<span class="star ' . $fclass . '">&nbsp;</span>';
                                }
                                $fieldValue .= '<span class="score">' . __("%1 stars", $rating_value) . '</span>';
                                $fieldValue .= '</div>';
                            }
                            break;
                        case 'product_field':
                            if ($fieldValue) {
                                $product = $this->getLoadProduct($fieldValue);
                                $urlProduct = $this->helperBackend->getUrl(
                                    'catalog/product/edit',
                                    [
                                        'id' => $product->getId()
                                    ]
                                );
                                $image = $product->getImage();
                                $urlImage = $this->getBaseMediaUrl() . 'catalog/product' . $image;
                                $image_alt = $product->getName();
                                $image_alt = @trim($image_alt);
                                $image_alt = str_replace(['"', "'"], "", $image_alt);
                                $tmp['image'] = "<img class='admin__control-thumbnail' src='" .
                                    $urlImage . "' width='35px' alt='" . $image_alt . "'/>";
                                if (empty($tmp['label'])) {
                                    $tmp['label'] = $product->getName();
                                }
                                $fieldValue = '<br/><a href="' . $urlProduct .
                                    '" target="_BLANK">' . $product->getName() . '</a>';
                                $price = $this->formatPrice($product->getFinalPrice());
                                if ($price) {
                                    $fieldValue .= '<br/>' . __("Price: %1", $price);
                                }
                            }
                            break;
                        case 'digital_signature':
                            $signatureFileType = $postData[$fieldId . "_filetype"] ?? "";
                            $signatureFileUrl = $postData[$fieldId] ?? "";
                            $signatureFileType = $signatureFileType ? @trim($signatureFileType) : "png";
                            $imageBase64 = str_replace("data:image/png;base64,", "", $signatureFileUrl);
                            $fileName = "eg" . $fieldId . $this->helper->getTimezoneDateTime();
                            $fileName = str_replace(["+",":"," "], "_", $fileName);
                            $filePath = $this->helper->saveImageFile(
                                $imageBase64,
                                $fileName,
                                $signatureFileType,
                                "esignature"
                            );
                            if ($filePath) {
                                $signatureFileUrl = $this->helper->getMediaImageUrl($filePath);
                            }
                            $tmp['image'] = "<img class='admin__control-digital-signature' src='" .
                                $signatureFileUrl . "' width='310px' alt='" .
                                __("E-Signature") . "'/>";
                            $tmp['image_type'] = $signatureFileType;
                            $tmp['is_blob'] = true;
                            $fieldValue = $signatureFileUrl;
                            break;
                        default:
                            if ($fieldValue) {
                                $fieldValue = strip_tags($fieldValue);
                                $fieldValue = @trim($fieldValue);
                                $fieldValue = is_numeric($fieldValue) ? $fieldValue : __($fieldValue);
                            }
                            break;
                    }
                    $tmp['value'] = $fieldValue;
                    $tmp['phone'] = $tmpPhone;
                    $formData[] = $tmp;
                    // }
                }
                if (($is_subscription || $subscription_all) && $emails) {
                    $this->subscriptionListEmails($emails);
                }

                if ($fullPhones) {
                    $this->subscriptionListPhones($fullPhones, $postData, $this->getId());
                }

                return $formData;
            }
        }
        return false;
    }

    /**
     * Subscription list phones
     */
    public function subscriptionListPhones($phones, $postData, $formId = 0): static
    {
        $this->_eventManager->dispatch(
            'formbuilder_substription_phones',
            ['model' => $this, 'phones' => $phones, "formId" => $formId, "postData" => $postData]
        );
        //call function of Lof_SmsNotification for subscription
        return $this;
    }

    /**
     * @return array|mixed|void
     */
    public function getFields()
    {
        $fields = json_decode('[' . $this->getData('design') . ']', true);
        if (isset($fields[0]['fields'])) {
            $fields[0] = $fields[0]['fields'];
            $fls = [];
            foreach ($fields[0] as $k => $v) {
                if (isset($v['field_type'])) {
                    $fls[] = $v;
                }
            }
            $fields[0] = $fls;
        }
        if (isset($fields[0])) {
            $tmpFields = [];
            foreach ($fields[0] as &$_field) {
                if (isset($tmpFields[$_field['cid']])) {
                    $_field['cid'] = $_field['cid'] . 'duplicate';
                }
                $tmpFields[$_field['cid']] = $_field;
            }

            return $fields[0];
        }
    }

    /**
     * @param string $street
     * @param string $city
     * @param string $state
     * @param string $zipcode
     * @param string $country
     * @param string $street2
     * @return string
     * @throws NoSuchEntityException
     */
    public function formatAddress(
        string $street = "",
        string $city = "",
        string $state = "",
        string $zipcode = "",
        string $country = "",
        string $street2 = ""
    ): string {
        $address_format = $this->helper->getConfig("field_templates/address");
        $data = [
            "street" => $street,
            "street2" => $street2,
            "city" => $city,
            "region" => $state,
            "postcode" => $zipcode,
            "country" => $country
        ];
        $street2 = $street2 ? (' ' . $street2) : '';
        if ($address_format == '') {
            return $street . $street2 . ', ' . $city . ', ' . $state . ', ' . $zipcode . ', ' . $country;
        }
        return $this->filterManager->template($address_format, ['variables' => $data]);
    }

    /**
     * @param $sku
     * @return Product|bool
     */
    public function getLoadProduct($sku): Product|bool
    {
        $productCollection = $this->productloader->create()->getCollection();
        $productCollection->addAttributeToSelect('entity_id', 'name', 'price', 'image')->addAttributeToFilter(
            'sku',
            ['eq' => $sku]
        );
        $collection = $productCollection->load();
        if ($collection->getSize()) {
            $productId = $collection->getFirstItem()->getEntityId();
            return $this->productloader->create()->load((int)$productId);
        } else {
            return false;
        }
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBaseMediaUrl(): string
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @param $price
     * @return float|string
     */
    public function formatPrice($price)
    {
        $objectManager = ObjectManager::getInstance();
        $priceHelper = $objectManager->create(\Magento\Framework\Pricing\Helper\Data::class);
        return $priceHelper->currency($price, true, false);
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getFormLink(): mixed
    {
        return $this->helper->getConfig('general_settings/route');
    }

    public function getDataProducts($id): ?array
    {
        $objectManager = ObjectManager::getInstance();
        $productCollection = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $collection = $productCollection->addAttributeToSelect('name', 'price', 'image')
            ->addAttributeToFilter('entity_id', ['eq' => $id])->load();
        return $collection->getData();
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Form::class);
    }

    /**
     * @param string $alias
     * @return $this
     * @throws LocalizedException
     */
    public function loadByAlias(string $alias = ""): static
    {
        if ($alias) {
            $this->_beforeLoad($alias, 'identifier');
            $this->_getResource()->load($this, $alias, 'identifier');
            $this->_afterLoad();
            $this->setOrigData();
            $this->_hasDataChanges = false;
            $this->updateStoredData();
        }
        return $this;
    }

    /**
     * Synchronize object's stored data with the actual data
     *
     * @return void
     */
    private function updateStoredData(): void
    {
        if (isset($this->_data)) {
            $this->storedData = $this->_data;
        } else {
            $this->storedData = [];
        }
    }

    /**
     * Get form_id
     *
     * @return int|null
     */
    public function getFormId(): ?int
    {
        return $this->getData(self::FORM_ID);
    }

    /**
     * Get form title
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Get status
     *
     * @return int|null
     */
    public function getStatus(): ?int
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Get identifier
     *
     * @return string|null
     */
    public function getIdentifier(): ?string
    {
        return $this->getData(self::IDENTIFIER);
    }

    /**
     * Get email_receive
     *
     * @return string|null
     */
    public function getEmailReceive(): ?string
    {
        return $this->getData(self::EMAIL_RECEIVE);
    }

    /**
     * Get thanks_email_template
     *
     * @return string|null
     */
    public function getThanksEmailTemplate(): ?string
    {
        return $this->getData(self::THANKS_EMAIL_TEMPLATE);
    }

    /**
     * Get email_template
     *
     * @return string|null
     */
    public function getEmailTemplate(): ?string
    {
        return $this->getData(self::EMAIL_TEMPLATE);
    }

    /**
     * Get show_captcha
     *
     * @return int|null
     */
    public function getShowCaptcha(): ?int
    {
        return $this->getData(self::SHOW_CAPTCHA);
    }

    /**
     * Get show_toplink
     *
     * @return int|null
     */
    public function getShowToplink(): ?int
    {
        return $this->getData(self::SHOW_TOP_LINKS);
    }

    /**
     * Get submit_button_text
     *
     * @return string|null
     */
    public function getSubmitButtonText(): ?string
    {
        return $this->getData(self::SUBMIT_BUTTON_TEXT);
    }

    /**
     * Get success_message
     *
     * @return string|null
     */
    public function getSuccessMessage(): ?string
    {
        return $this->getData(self::SUCCESS_MESSAGE);
    }

    /**
     * Get creation_time
     *
     * @return string|null
     */
    public function getCreationTime(): ?string
    {
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * Get update_time
     *
     * @return string|null
     */
    public function getUpdateTime(): ?string
    {
        return $this->getData(self::UPDATE_TIME);
    }

    /**
     * Get before_form_content
     *
     * @return string|null
     */
    public function getBeforeFormContent(): ?string
    {
        return $this->getData(self::BEFORE_FORM_CONTENT);
    }

    /**
     * Get after_form_content
     *
     * @return string|null
     */
    public function getAfterFormContent(): ?string
    {
        return $this->getData(self::AFTER_FORM_CONTENT);
    }

    /**
     * Get design
     *
     * @return string|null
     */
    public function getDesign(): ?string
    {
        return $this->getData(self::DESIGN);
    }

    /**
     * Get page_title
     *
     * @return string|null
     */
    public function getPageTitle(): ?string
    {
        return $this->getData(self::PAGE_TITLE);
    }

    /**
     * Get redirect_link
     *
     * @return string|null
     */
    public function getRedirectLink(): ?string
    {
        return $this->getData(self::REDIRECT_LINK);
    }

    /**
     * Get page_layout
     *
     * @return string|null
     */
    public function getPageLayout(): ?string
    {
        return $this->getData(self::PAGE_LAYOUT);
    }

    /**
     * Get layout_update_xml
     *
     * @return string|null
     */
    public function geLayoutUpdateXml(): string|null
    {
        return $this->getData(self::LAYOUT_UPDATE_XML);
    }

    /**
     * Get meta_keywords
     *
     * @return string|null
     */
    public function getMetaKeywords(): string|null
    {
        return $this->getData(self::META_KEYWORDS);
    }

    /**
     * Get meta_description
     *
     * @return string|null
     */
    public function getMetaDescription(): string|null
    {
        return $this->getData(self::META_DESCRIPTION);
    }

    /**
     * Get thankyou_field
     *
     * @return string|null
     */
    public function getThankyouField(): string|null
    {
        return $this->getData(self::THANKYOU_FIELD);
    }

    /**
     * Get thankyou_email_template
     *
     * @return string|null
     */
    public function getThankyouEmailTemplate(): string|null
    {
        return $this->getData(self::THANKYOU_EMAIL_TEMPLATE);
    }

    /**
     * Get submit_text_color
     *
     * @return string|null
     */
    public function getSubmitTextColor(): string|null
    {
        return $this->getData(self::SUBMIT_TEXT_COLOR);
    }

    /**
     * Get submit_background_color
     *
     * @return string|null
     */
    public function getSubmitBackgroundColor(): string|null
    {
        return $this->getData(self::SUBMIT_BACKGROUND_COLOR);
    }

    /**
     * Get submit_hover_color
     *
     * @return string|null
     */
    public function getSubmitHoverColor(): string|null
    {
        return $this->getData(self::SUBMIT_HOVER_COLOR);
    }

    /**
     * Get input_hover_color
     *
     * @return string|null
     */
    public function getInputHoverColor(): string|null
    {
        return $this->getData(self::INPUT_HOVER_COLOR);
    }

    /**
     * Get custom_template
     *
     * @return string|null
     */
    public function getCustomTemplate(): string|null
    {
        return $this->getData(self::CUSTOM_TEMPLATE);
    }

    /**
     * Get sender_email_field
     *
     * @return string|null
     */
    public function getSenderEmailField(): string|null
    {
        return $this->getData(self::SENDER_EMAIL_FIELD);
    }

    /**
     * Get sender_name_field
     *
     * @return string|null
     */
    public function getSenderNameField(): string|null
    {
        return $this->getData(self::SENDER_NAME_FIELD);
    }

    /**
     * Get tags
     *
     * @return string|null
     */
    public function getTags(): string|null
    {
        return $this->getData(self::TAGS);
    }

    /**
     * Set form_id
     *
     * @param int $id
     * @return $this
     */
    public function setFormId(int $id): FormbuilderInterface
    {
        $this->setData(self::FORM_ID, $id);
        return $this;
    }

    /**
     * Set tags
     *
     * @param string $tags
     * @return $this
     */
    public function setTags(string $tags): static
    {
        $this->setData(self::TAGS, $tags);
        return $this;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): static
    {
        $this->setData(self::TITLE, $title);
        return $this;
    }

    /**
     * Set status
     *
     * @param int $status
     * @return $this
     */
    public function setStatus(int $status): static
    {
        $this->setData(self::STATUS, $status);
        return $this;
    }

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return $this
     */
    public function setIdentifier(string $identifier): static
    {
        $this->setData(self::IDENTIFIER, $identifier);
        return $this;
    }

    /**
     * Set email_receive
     *
     * @param int $emailReceive
     * @return $this
     *
     */
    public function setEmailReceive(int $emailReceive): static
    {
        $this->setData(self::EMAIL_RECEIVE, $emailReceive);
        return $this;
    }

    /**
     * Set thanks_email_template
     *
     * @param string $thanksEmailTemplate
     * @return $this
     */
    public function setThanksEmailTemplate(string $thanksEmailTemplate): static
    {
        $this->setData(self::THANKS_EMAIL_TEMPLATE, $thanksEmailTemplate);
        return $this;
    }

    /**
     * Set email_template
     *
     * @param string $emailTemplate
     * @return $this
     */
    public function setEmailTemplate(string $emailTemplate): static
    {
        $this->setData(self::EMAIL_TEMPLATE, $emailTemplate);
        return $this;
    }

    /**
     * Set show_captcha
     *
     * @param int $showCaptcha
     * @return $this
     */
    public function setShowCaptcha(int $showCaptcha): static
    {
        $this->setData(self::SHOW_CAPTCHA, $showCaptcha);
        return $this;
    }

    /**
     * Set show_toplink
     *
     * @param int $showToplink
     * @return $this
     */
    public function setShowToplink(int $showToplink): static
    {
        $this->setData(self::SHOW_TOP_LINKS, $showToplink);
        return $this;
    }

    /**
     * Set submit_button_text
     *
     * @param string $submitButtonText
     * @return $this
     */
    public function setSubmitButtonText(string $submitButtonText): static
    {
        $this->setData(self::SUBMIT_BUTTON_TEXT, $submitButtonText);
        return $this;
    }

    /**
     * Set success_message
     *
     * @param string $successMessage
     * @return $this
     */
    public function setSuccessMessage(string $successMessage): static
    {
        $this->setData(self::SUCCESS_MESSAGE, $successMessage);
        return $this;
    }

    /**
     * Set creation_time
     *
     * @param string $creationTime
     * @return $this
     */
    public function setCreationTime(string $creationTime): static
    {
        $this->setData(self::CREATION_TIME, $creationTime);
        return $this;
    }

    /**
     * Set update_time
     *
     * @param string $updateTime
     * @return $this
     */
    public function setUpdateTime(string $updateTime): static
    {
        $this->setData(self::UPDATE_TIME, $updateTime);
        return $this;
    }

    /**
     * Set before_form_content
     *
     * @param string $beforeFormContent
     * @return $this
     */
    public function setBeforeFormContent(string $beforeFormContent): static
    {
        $this->setData(self::BEFORE_FORM_CONTENT, $beforeFormContent);
        return $this;
    }

    /**
     * Set after_form_content
     *
     * @param string $afterFormContent
     * @return $this
     */
    public function setAfterFormContent(string $afterFormContent): static
    {
        $this->setData(self::AFTER_FORM_CONTENT, $afterFormContent);
        return $this;
    }

    /**
     * Set design
     *
     * @param string $design
     * @return $this
     */
    public function setDesign(string $design): static
    {
        $this->setData(self::DESIGN, $design);
        return $this;
    }

    /**
     * Set page_title
     *
     * @param string $pageTitle
     * @return $this
     */
    public function setPageTitle(string $pageTitle): static
    {
        $this->setData(self::PAGE_TITLE, $pageTitle);
        return $this;
    }

    /**
     * Set redirect_link
     *
     * @param string $redirectLink
     * @return $this
     */
    public function setRedirectLink(string $redirectLink): static
    {
        $this->setData(self::REDIRECT_LINK, $redirectLink);
        return $this;
    }

    /**
     * Set page_layout
     *
     * @param string $pageLayout
     * @return $this
     */
    public function setPageLayout(string $pageLayout): static
    {
        $this->setData(self::PAGE_LAYOUT, $pageLayout);
        return $this;
    }

    /**
     * Set layout_update_xml
     *
     * @param string $layoutUpdateXml
     * @return $this
     */
    public function setLayoutUpdateXml(string $layoutUpdateXml): static
    {
        $this->setData(self::LAYOUT_UPDATE_XML, $layoutUpdateXml);
        return $this;
    }

    /**
     * Set meta_keywords
     *
     * @param string $metaKeywords
     * @return $this
     */
    public function setMetaKeywords(string $metaKeywords): static
    {
        $this->setData(self::META_KEYWORDS, $metaKeywords);
        return $this;
    }

    /**
     * Set meta_description
     *
     * @param string $metaDescription
     * @return $this
     */
    public function setMetaDescription(string $metaDescription): static
    {
        $this->setData(self::META_DESCRIPTION, $metaDescription);
        return $this;
    }

    /**
     * Set thankyou_field
     *
     * @param string $thankyouField
     * @return $this
     */
    public function setThankyouField(string $thankyouField): static
    {
        $this->setData(self::THANKYOU_FIELD, $thankyouField);
        return $this;
    }

    /**
     * Set thankyou_email_template
     *
     * @param string $thankyouEmailTemplate
     * @return $this
     */
    public function setThankyouEmailTemplate(string $thankyouEmailTemplate): static
    {
        $this->setData(self::THANKYOU_EMAIL_TEMPLATE, $thankyouEmailTemplate);
        return $this;
    }

    /**
     * Set submit_text_color
     *
     * @param string $submitTextColor
     * @return $this
     */
    public function setSubmitTextColor(string $submitTextColor): static
    {
        $this->setData(self::SUBMIT_TEXT_COLOR, $submitTextColor);
        return $this;
    }

    /**
     * Set submit_background_color
     *
     * @param string $submitBackgroundColor
     * @return $this
     */
    public function setSubmitBackgroundColor(string $submitBackgroundColor): static
    {
        $this->setData(self::SUBMIT_BACKGROUND_COLOR, $submitBackgroundColor);
        return $this;
    }

    /**
     * Set submit_hover_color
     *
     * @param string $submitHoverColor
     * @return $this
     */
    public function setSubmitHoverColor(string $submitHoverColor): static
    {
        $this->setData(self::SUBMIT_HOVER_COLOR, $submitHoverColor);
        return $this;
    }

    /**
     * Set input_hover_color
     *
     * @param string $inputHoverColor
     * @return $this
     */
    public function setInputHoverColor(string $inputHoverColor): static
    {
        $this->setData(self::INPUT_HOVER_COLOR, $inputHoverColor);
        return $this;
    }

    /**
     * Set custom_template
     *
     * @param string $customTemplate
     * @return $this
     */
    public function setCustomTemplate(string $customTemplate): static
    {
        $this->setData(self::CUSTOM_TEMPLATE, $customTemplate);
        return $this;
    }

    /**
     * Set sender_email_field
     *
     * @param string $senderEmailField
     * @return $this
     */
    public function setSenderEmailField(string $senderEmailField): static
    {
        $this->setData(self::SENDER_EMAIL_FIELD, $senderEmailField);
        return $this;
    }

    /**
     * Set sender_name_field
     *
     * @param string $senderNameField
     * @return $this
     */
    public function setSenderNameField(string $senderNameField): static
    {
        $this->setData(self::SENDER_NAME_FIELD, $senderNameField);
        return $this;
    }

    /**
     * Set enable_tracklink
     *
     * @param int $enableTracklink
     * @return $this
     */
    public function setEnableTracklink(int $enableTracklink): static
    {
        $this->setData(self::ENABLE_TRACKLINK, $enableTracklink);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getEnableTracklink(): ?int
    {
        return $this->getData(self::ENABLE_TRACKLINK);
    }

    /**
     * @return int|null
     */
    public function getCustomerGroups(): mixed
    {
        return $this->getData(self::CUSTOMER_GROUPS);
    }

    /**
     * @param mixed $customerGroups
     * @return $this
     */
    public function setCustomerGroups(mixed $customerGroups): static
    {
        return $this->setData(self::CUSTOMER_GROUPS, $customerGroups);
    }

    /**
     * @return mixed
     */
    public function getDesignFields(): mixed
    {
        return $this->getData(self::DESIGN_FIELDS);
    }

    /**
     * @param array $designFields
     * @return $this
     */
    public function setDesignFields(array $designFields): static
    {
        return $this->setData(self::DESIGN_FIELDS, $designFields);
    }
}
