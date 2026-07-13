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

namespace Lof\Formbuilder\Helper;

use Exception;
use Laminas\Validator\EmailAddress as EmailAddressAlias;
use Magento\Checkout\Model\Session;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Currency\Exception\CurrencyException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Validator\EmailAddress;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Data extends AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var FilterProvider
     */
    protected FilterProvider $filterProvider;

    /**
     * @var TimezoneInterface
     */
    protected TimezoneInterface $localeDate;

    /**
     * @var DateTime
     */
    protected DateTime $dateTime;

    /**
     * @var CurrencyInterface
     */
    protected CurrencyInterface $localeCurrency;

    /**
     * @var ObjectManagerInterface
     */
    protected ObjectManagerInterface $objectManager;

    /**
     * @var SessionFactory
     */
    protected SessionFactory $customerSession;

    /**
     * @var Session
     */
    protected Session $checkoutSession;

    /**
     * @var Registry
     */
    protected Registry $coreRegistry;

    /**
     * @var FilterManager
     */
    protected FilterManager $filterManager;

    /**
     * @var null
     */
    protected $logger = null;

    /**
     * @var DataPersistorInterface|null
     */
    private ?DataPersistorInterface $dataPersistor = null;

    /**
     * @var array|null
     */
    private ?array $postData = null;

    /**
     * @var Trackcode
     */
    protected Trackcode $trackcode;

    /**
     * @var WriteInterface
     */
    protected WriteInterface $mediaDirectory;

    /**
     * Serializer.
     *
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * @param Context $context
     * @param FilterProvider $filterProvider
     * @param StoreManagerInterface $storeManager
     * @param CurrencyInterface $localeCurrency
     * @param TimezoneInterface $localeDate
     * @param ObjectManagerInterface $objectManager
     * @param SessionFactory $customerSession
     * @param Session $checkoutSession
     * @param Registry $coreRegistry
     * @param FilterManager $filterManager
     * @param Trackcode $trackcode
     * @param Filesystem $filesystem
     * @param DateTime $dateTime
     * @param SerializerInterface $serializer
     * @throws FileSystemException
     * @throws NoSuchEntityException
     */
    public function __construct(
        Context $context,
        FilterProvider $filterProvider,
        StoreManagerInterface $storeManager,
        CurrencyInterface $localeCurrency,
        TimezoneInterface $localeDate,
        ObjectManagerInterface $objectManager,
        SessionFactory $customerSession,
        Session $checkoutSession,
        Registry $coreRegistry,
        FilterManager $filterManager,
        Trackcode $trackcode,
        Filesystem $filesystem,
        DateTime $dateTime,
        SerializerInterface $serializer
    ) {
        parent::__construct($context);
        $this->filterProvider = $filterProvider;
        $this->storeManager = $storeManager;
        $this->localeDate = $localeDate;
        $this->localeCurrency = $localeCurrency;
        $this->objectManager = $objectManager;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->coreRegistry = $coreRegistry;
        $this->filterManager = $filterManager;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->filesystem = $filesystem;
        $this->trackcode = $trackcode;
        $this->dateTime = $dateTime;

        $chunks = $this->getConfig("message_setting/chunks", null, 1);
        $letters = $this->getConfig("message_setting/letters", null, 9);
        $separate_text = $this->getConfig("message_setting/separate_text", null, "-");

        $this->trackcode->numberChunks = (int)$chunks;
        $this->trackcode->numberLettersPerChunk = (int)$letters;
        $this->trackcode->separateChunkText = (int)$separate_text;

        $this->serializer = $serializer;
    }

    /**
     * @param $string
     * @return bool
     */
    public function isJSON($string): bool
    {
        return is_string($string) && is_array(json_decode($string, true)) &&
        (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * @param $params
     * @return string|bool
     */
    public function encodeData($params): string|bool
    {
        return $this->serializer->serialize($params);
    }

    /**
     * Decode string to array
     *
     * @return mixed|array
     */
    public function decodeData($string)
    {
        if (!$this->isJSON($string)) {
            return @unserialize($string);
        }
        return $this->serializer->unserialize($string);
    }

    /**
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore(): StoreInterface
    {
        return $this->storeManager->getStore();
    }

    /**
     * @param string $dateTime
     * @return string
     * @throws Exception
     */
    public function getTimezoneDateTime(string $dateTime = "today"): string
    {
        if ($dateTime === "today" || !$dateTime) {
            $dateTime = $this->dateTime->gmtDate();
        }

        return $this->localeDate
            ->date(
                new \DateTime($dateTime)
            )->format('Y-m-d H:i:s');
    }

    /**
     * @return string
     */
    public function generateTrackcode(): string
    {
        return $this->trackcode->generate();
    }

    /**
     * @param null $message
     * @return string
     */
    public function getQrcodeChart($message = null): string
    {
        if ($message) {
            $qrcode = $message->getQrcode();
            if ($qrcode) {
                $track_url = str_replace([" ", ":", "=", "&", "?"], ["+", "%3A", "%3D", "%26", "%3F"], $qrcode);
                return "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=" . $track_url . "&choe=UTF-8";
            }
        }
        return "";
    }

    /**
     * @param null $message
     * @return string
     * @throws NoSuchEntityException
     */
    public function getQrcodeTracklink($message = null): string
    {
        if ($message) {
            $qrcode = $message->getQrcode();
            if ($qrcode) {
                $route = $this->getConfig("general_settings/route");
                $route = $route ? $route : "formbuilder";
                $link_trackorder = $this->storeManager->getStore()->getBaseUrl() . $route . '/view/' . $qrcode;
                return "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=" .
                    $link_trackorder . "&choe=UTF-8";
            }
        }
        return "";
    }

    /**
     * @param null $message
     * @return string
     * @throws NoSuchEntityException
     */
    public function getTrackUrl($message = null): string
    {
        if ($message) {
            $qrcode = $message->getQrcode();
            if ($qrcode) {
                $route = $this->getConfig("general_settings/route");
                $route = $route ? $route : "formbuilder";
                return $this->storeManager->getStore()->getBaseUrl() . $route . '/view/' . $qrcode;
            }
        }
        return '';
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBaseUrl(): string
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }

    /**
     * @param $str
     * @return string
     * @throws Exception
     */
    public function filter($str): string
    {
        $str = $str ? $this->formatCustomVariables($str) : "";
        return $this->filterProvider->getPageFilter()->filter($str);
    }

    /**
     * @param $key
     * @param null $store
     * @param null $default
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getConfig($key, $store = null, $default = null): mixed
    {
        $store = $this->storeManager->getStore($store);

        $result = $this->scopeConfig->getValue(
            'lofformbuilder/' . $key,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        if ($default != null) {
            return $result ? $result : $default;
        } else {
            return $result;
        }
    }

    /**
     * @return string $sender_email
     * @throws NoSuchEntityException
     */
    public function getSenderEmail(): string
    {
        $storeScope = ScopeInterface::SCOPE_STORE;
        $uCode = $this->getConfig('email_settings/sender_email_identity');
        return $this->scopeConfig->getValue('trans_email/ident_' . $uCode . '/name', $storeScope);
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBaseMediaUrl(): string
    {
        $store = $this->storeManager->getStore();
        return $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Get media file path
     *
     * @return string
     */
    public function getMediaFilePath(): string
    {
        return 'lof/formbuilder/files';
    }

    /**
     * Get upload media file path
     *
     * @return string
     */
    public function getUploadMediaFilePath(): string
    {
        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $mediaFolder = $this->getMediaFilePath();
        return $mediaDirectory->getAbsolutePath($mediaFolder);
    }

    /**
     * @param null $date
     * @param int $format
     * @param bool $showTime
     * @param null $timezone
     * @return string
     * @throws \Exception
     */
    public function formatDate(
        $date = null,
        int $format = \IntlDateFormatter::SHORT,
        bool $showTime = false,
        $timezone = null
    ): string {
        $date = $date instanceof \DateTimeInterface ? $date : new \DateTime($date);
        return $this->localeDate->formatDateTime(
            $date,
            $format,
            $showTime ? $format : \IntlDateFormatter::NONE,
            null,
            $timezone
        );
    }

    /**
     * @param $date
     * @param string $type
     * @return string
     * @throws Exception
     */
    public function getFormatDate($date, string $type = 'full'): string
    {
        $result = '';
        switch ($type) {
            case 'full':
                $result = $this->formatDate($date, \IntlDateFormatter::FULL);
                break;
            case 'long':
                $result = $this->formatDate($date, \IntlDateFormatter::LONG);
                break;
            case 'medium':
                $result = $this->formatDate($date, \IntlDateFormatter::MEDIUM);
                break;
            case 'short':
                $result = $this->formatDate($date, \IntlDateFormatter::SHORT);
                break;
        }

        return $result;
    }

    /**
     * @return string|null
     * @throws NoSuchEntityException
     * @throws CurrencyException
     */
    public function getSymbol(): ?string
    {
        $currency = $this->localeCurrency->getCurrency($this->storeManager->getStore()->getCurrentCurrencyCode());
        $symbol = $currency->getSymbol() ? $currency->getSymbol() : $currency->getShortName();

        if (!$symbol) {
            $symbol = '';
        }
        return $symbol;
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getMediaUrl(): mixed
    {
        return $this->objectManager->get(StoreManagerInterface::class)
            ->getStore()
            ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @return string
     */
    public function getFieldPrefix(): string
    {
        return 'loffield_';
    }

    /**
     * @return bool|mixed|null
     */
    public function getCurrentProduct(): mixed
    {
        if ($this->coreRegistry->registry('product')) {
            return $this->coreRegistry->registry('product');
        }
        return false;
    }

    /**
     * @return bool|mixed|null
     */
    public function getCurrentCategory(): mixed
    {
        if ($this->coreRegistry->registry('current_category')) {
            return $this->coreRegistry->registry('current_category');
        }
        return false;
    }

    /**
     * @return CartInterface|Quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuote(): CartInterface|Quote
    {
        return $this->checkoutSession->getQuote();
    }

    /**
     * @param string $customerId
     * @return Customer
     */
    public function getCustomer(string $customerId = '')
    {
        return $this->customerSession->create()->getCustomer();
    }

    /**
     * @param string $identifier
     * @return string
     * @throws NoSuchEntityException
     */
    public function getFormUrl(string $identifier = ""): string
    {
        if ($identifier) {
            $route = $this->getConfig('general_settings/route');
            if ($route != '') {
                $route = $route . '/';
            }
            $baseUrl = $this->storeManager->getStore()->getBaseUrl();
            return $baseUrl . $route . $identifier;
        }
        return "";
    }

    /**
     * @param $str
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function formatCustomVariables($str): string
    {
        $customer = $this->getCustomer();
        $quote = $this->getQuote();
        $category = $this->getCurrentCategory();
        $store = $this->storeManager->getStore();
        $product = $this->getCurrentProduct();
        if ($this->_moduleManager->isEnabled('Lof_HidePrice')) {
            if (!$product) {
                $product = 'product_hideprice';
            }
        }
        $data = [
            "customer" => $customer,
            "quote" => $quote,
            "product" => $product,
            "category" => $category,
            "store" => $store
        ];
        return $this->filterManager->template($str, ['variables' => $data]);
    }

    /**
     * @param array $submitted_data
     * @return array
     */
    public function getEmailsFromData(array $submitted_data = []): array
    {
        $emails = [];
        $pattern = '/[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})/i';
        if ($submitted_data) {
            foreach ($submitted_data as $val) {
                preg_match_all($pattern, $val, $matches);
                if ($matches && isset($matches[0]) && $matches[0]) {
                    if (is_array($matches[0])) {
                        $emails = array_merge($emails, $matches[0]);
                    } else {
                        $emails[] = $matches[0];
                    }
                }
            }
        }

        if ($emails) {
            $tmp_emails = [];
            foreach ($emails as $val) {
                if (!in_array($val, $tmp_emails)) {
                    $tmp_emails[] = $val;
                }
            }
            $emails = $tmp_emails;
        }
        return $emails;
    }

    /**
     * @param $e
     * @return void
     * @throws NoSuchEntityException
     */
    public function writeLogData($e): void
    {
        if ($this->getConfig("general_settings/enable_debug")) {
            if (!$this->logger) {
                $this->logger = ObjectManager::getInstance()->get(LoggerInterface::class);
            }
            $this->logger->addDebug($e);
        }
    }

    /**
     * Get value from POST by key
     *
     * @param string $key
     * @return string
     */
    public function getPostValue(string $key): string
    {
        if (null === $this->postData) {
            $this->postData = (array)$this->getDataPersistor()->get('formbuilder');
            $this->getDataPersistor()->clear('formbuilder');
        }

        if (isset($this->postData[$key])) {
            return (string)$this->postData[$key];
        }

        return '';
    }

    /**
     * Get Data Persistor
     *
     * @return DataPersistorInterface
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
     * @param $field
     * @return string|string[]
     */
    public function getFieldId($field): array|string
    {
        $cid = $field['cid'] ?? '';
        $fieldId = $field['field_id'] ?? '';
        $fieldId = @trim($fieldId);
        $fieldId = str_replace(" ", "-", $fieldId);

        if ($fieldId) {
            $cid = $fieldId;
        }
        return $cid;
    }

    /**
     * @param $getDate
     * @return string
     */
    public function formatDateFormBuilder($getDate): string
    {
        $formatDate = $this->scopeConfig->getValue(
            'lofformbuilder/general_settings/dateformat',
            ScopeInterface::SCOPE_STORE
        );
        return date($formatDate, strtotime($getDate));
    }

    /**
     * Validate email address is valid
     *
     * @param string $value
     * @return bool
     */
    public function validateEmailAddress(string $value): bool
    {
        $validator = new EmailAddress();
        $validator->setMessage(
            __('"%1" invalid type entered.', $value),
            EmailAddressAlias::INVALID
        );
        $phpValidateEmail = filter_var($value, FILTER_VALIDATE_EMAIL);
        $coreValidateEmail = true;
        if (!$validator->isValid($value)) {
            $coreValidateEmail = false;
        }

        return $phpValidateEmail && $coreValidateEmail;
    }

    /**
     * Save image File
     * @param string $fileContent
     * @param string $fileName
     * @param string $fileType
     * @param string $folder
     * @return string
     */
    public function saveImageFile(
        string $fileContent,
        string $fileName,
        string $fileType = "png",
        string $folder = "barcode"
    ): string {
        $folder = $folder ? @trim($folder) : "barcode";
        $lofDirPath     = $this->mediaDirectory->getAbsolutePath("lof");
        $moduleDirPath = $this->mediaDirectory->getAbsolutePath("lof/formbuilder");
        $bannerimageDirPath = $this->mediaDirectory->getAbsolutePath("lof/formbuilder/" . $folder);
        if (!file_exists($lofDirPath)) {
            mkdir($lofDirPath, 0777, true);
        }
        if (!file_exists($moduleDirPath)) {
            mkdir($moduleDirPath, 0777, true);
        }
        if (!file_exists($bannerimageDirPath)) {
            mkdir($bannerimageDirPath, 0777, true);
        }
        $baseTmpPath  = "lof/formbuilder/" . $folder . "/";
        $target       = $this->mediaDirectory->getAbsolutePath($baseTmpPath);
        $fileName = $fileName . "." . $fileType;
        if (!file_exists($target . $fileName)) {
            //$data = $fileContent;
            $data = str_replace(' ', '+', $fileContent);
            $data = base64_decode($data);
            @file_put_contents($target . $fileName, $data);
        }
        if (file_exists($target . $fileName)) {
            return $baseTmpPath . $fileName;
        }
        return "";
    }

    /**
     * @param $filePath
     * @return string
     * @throws NoSuchEntityException
     */
    public function getMediaImageUrl($filePath): string
    {
        $target = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        return $target . $filePath;
    }

    /**
     * @param $dataArray
     * @return array
     */
    public function xssCleanArray($dataArray): array
    {
        $result = [];
        if (is_array($dataArray)) {
            foreach ($dataArray as $key => $val) {
                $val = $this->xssClean($val);
                $result[$key] = $val;
            }
        }
        return $result;
    }

    /**
     * @param $data
     * @return string|string[]|null
     */
    public function xssClean($data): array|string|null
    {
        if (!is_string($data)) {
            return $data;
        }
        // Fix &entity\n;
        $data = str_replace(['&amp;', '&lt;', '&gt;'], ['&amp;amp;', '&amp;lt;', '&amp;gt;'], $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        // Remove javascript: and vbscript: protocols
        $data = preg_replace(
            '#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu',
            '$1=$2nojavascript...',
            $data
        );
        $data = preg_replace(
            '#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu',
            '$1=$2novbscript...',
            $data
        );
        $data = preg_replace(
            '#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u',
            '$1=$2nomozbinding...',
            $data
        );

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace(
            '#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i',
            '$1>',
            $data
        );
        $data = preg_replace(
            '#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i',
            '$1>',
            $data
        );
        $data = preg_replace(
            '#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu',
            '$1>',
            $data
        );

        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace(
                '#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i',
                '',
                $data
            );
        } while ($old_data !== $data);

        // we are done...
        return $data;
    }
}
