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

use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class GenerateFile extends AbstractHelper
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
     * @var CurrencyInterface
     */
    protected CurrencyInterface $localeCurrency;

    /**
     * @var ObjectManagerInterface
     */
    protected ObjectManagerInterface $objectManager;

    /**
     * @var Session
     */
    protected Session $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected \Magento\Checkout\Model\Session $checkoutSession;

    /**
     * @var Registry
     */
    protected Registry $coreRegistry;

    /**
     * @var FilterManager
     */
    protected FilterManager $filterManager;

    /**
     * @var File
     */
    protected File $file;

    /**
     * @var FileFactory
     */
    protected FileFactory $fileFactory;

    /**
     * @var Filesystem
     */
    protected Filesystem $fileSystem;

    protected ?string $rootDir = null;
    /**
     * GenerateFile constructor.
     * @param Context $context
     * @param FilterProvider $filterProvider
     * @param StoreManagerInterface $storeManager
     * @param ObjectManagerInterface $objectManager
     * @param Registry $coreRegistry
     * @param FilterManager $filterManager
     * @param File $file
     * @param FileFactory $fileFactory
     * @param Filesystem $fileSystem
     */
    public function __construct(
        Context $context,
        FilterProvider $filterProvider,
        StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager,
        Registry $coreRegistry,
        FilterManager $filterManager,
        File $file,
        FileFactory $fileFactory,
        Filesystem $fileSystem
    ) {
        parent::__construct($context);
        $this->filterProvider = $filterProvider;
        $this->storeManager = $storeManager;
        $this->objectManager = $objectManager;
        $this->coreRegistry = $coreRegistry;
        $this->filterManager = $filterManager;
        $this->file = $file;
        $this->fileFactory = $fileFactory;
        $this->fileSystem = $fileSystem;
    }

    /**
     * @param $str
     * @return string
     * @throws \Exception
     */
    public function filter($str): string
    {
        return $str ? $this->filterProvider->getPageFilter()->filter($str) : "";
    }

    /**
     * @param $key
     * @param $store
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getConfig($key, $store = null): mixed
    {
        $store = $this->storeManager->getStore($store);
        $store->getWebsiteId();

        return $this->scopeConfig->getValue(
            'lofformbuilder/' . $key,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @return string
     */
    public function getRootDir(): string
    {
        if (!isset($this->rootDir)) {
            $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::VAR_DIR);
            $this->rootDir = $mediaDirectory->getAbsolutePath();
        }
        return $this->rootDir;
    }

    /**
     * @param $fileName
     * @param string $fileType
     * @return string
     */
    public function getFilePathWithName($fileName, string $fileType = "xml"): string
    {
        $fileName = $fileName . "." . $fileType;
        return $this->getRootDir() . $fileName;
    }

    /**
     * @param $formData
     * @param $params
     * @param $form
     * @return array
     * @throws \Exception
     */
    public function generateFormFile($formData, $params, $form): array
    {

        $generateToFile = $form->getData("generate_to_file");
        if ($generateToFile) {
            $fileName = $form->getData("identifier") . "_" . time();
            $fileType = "xml";
            $fileName = $fileName . "." . $fileType;
            $fileMetaType = 'application/xml';
            if ($fileType = "xml") {
                $fileMetaType = 'application/xml';
            }
            $filePath = $this->getFilePathWithName($fileName, $fileType);
            $fileContent = $this->getFileContent($formData, $params, $form);
            ob_start();
            $this->fileFactory->create(
                $fileName,
                $fileContent,
                DirectoryList::VAR_DIR,
                $fileMetaType
            );
            $output = ob_get_contents();
            ob_end_clean();
            return [$fileName, $filePath, $fileContent];
        }
        return [];
    }

    /**
     * @param $formData
     * @param $params
     * @param $form
     * @return string|string[]
     */
    public function getFileContent($formData, $params, $form): array|string
    {
        $fileContent = $form->getData("generate_to_file");
        $formId = $form->getId();
        $available_variables = [
            "{{brower}}",
            "{{ip_address}}",
            "{{http_host}}",
            "{{current_url}}",
            "{{customer_id}}",
            "{{loffield_%1%%2%}}"
        ];

        foreach ($available_variables as $variable) {
            if ($variable == "{{loffield_%1%%2%}}" && $formData) {
                $variable_key = str_replace("%2%", $formId, $variable);
                foreach ($formData as $field) {
                    $cid = $field['cid'] ?? '';
                    $fieldId = $field['field_id'] ?? '';
                    $fieldId = @trim($fieldId);
                    $fieldId = str_replace(" ", "-", $fieldId);

                    if ($fieldId) {
                        $cid = $fieldId;
                    }
                    $variable_key_tmp = str_replace("%1%", $cid, $variable_key);

                    $fieldValue = strip_tags($field['value']);
                    $fileContent = str_replace($variable_key_tmp, $fieldValue, $fileContent);
                }
            } else {
                $variable_name = str_replace(["{{", "}}"], "", $variable);
                if (isset($params[$variable_name])) {
                    $fileContent = str_replace($variable, $params[$variable_name], $fileContent);
                }

            }
        }
        return $fileContent;
    }
}
