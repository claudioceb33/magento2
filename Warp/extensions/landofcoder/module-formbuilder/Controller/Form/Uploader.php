<?php /**
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

use Lof\Formbuilder\Helper\Data;
use Lof\Formbuilder\Helper\Fields;
use Lof\Formbuilder\Model\Form;
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
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Module\Manager;
use Magento\Framework\Registry;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;
use RuntimeException;

class Uploader extends Action
{
    public const FILE_TYPES = 'jpg,JPG,jpeg,JPEG,gif,GIF,png,PNG,
    doc,DOC,docx,DOCX,pdf,PDF,zip,ZIP,tar,TAR,rar,RAR,tgz,TGZ,7zip,7ZIP,gz,GZ';

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

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
     * @var PageFactory
     */
    protected $resultPageFactory;

    protected $coreRegistry;

    protected $formFieldHelper;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Http
     */
    protected $httpRequest;

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
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
     * @throws FileSystemException
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
        DataPersistorInterface $dataPersistor
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
        parent::__construct($context);
    }

    protected function parseSize($size): float
    {
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        return round($size);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        try {
            $store = $this->storeManager->getStore();
            $formId = $this->getRequest()->getParam("form_id");
            $fieldId = $this->getRequest()->getParam("cid");
            $responseData = [];
            if ($formId && $_FILES && $fieldId) {
                $form = $this->form->load($formId);
                if ($form->getId()) {
                    if (
                        !isset($_FILES['file']['error']) ||
                        is_array($_FILES['file']['error'])
                    ) {
                        throw new RuntimeException(__('Invalid parameters.'));
                    }
                    switch ($_FILES['file']['error']) {
                        case UPLOAD_ERR_OK:
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            throw new RuntimeException('No file sent.');
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            throw new RuntimeException('Exceeded filesize limit.');
                        default:
                            throw new RuntimeException('Unknown errors.');
                    }
                    if ($fields = $form->getFields()) {
                        $mediaUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
                        $mediaDirectory = $this->_objectManager->get(Filesystem::class)
                            ->getDirectoryRead(DirectoryList::MEDIA);
                        $mediaFolder = 'lof/formbuilder/files';
                        $savePath = $mediaDirectory->getAbsolutePath($mediaFolder);
                        if ($store->getId()) {
                            $saveStorePath = $savePath . DIRECTORY_SEPARATOR . $store->getId();
                            if (!is_dir($saveStorePath)) {
                                $saveStorePath = $this->mkdir($savePath, $store->getId());
                            }
                            if ($saveStorePath) {
                                $savePath = $saveStorePath;
                                $mediaFolder .= "/" . $store->getId();
                            }
                        }

                        foreach ($fields as $field) {
                            $cid = $this->helper->getFieldId($field);
                            if ($field && $fieldId == $cid) {
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
                                $uploader = $this->_objectManager->create(
                                    \Magento\Framework\File\Uploader::class,
                                    ['fileId' => 'file']
                                );
                                $uploader->setAllowedExtensions($fieldTypes);
                                $uploader->setAllowRenameFiles(true);
                                $uploader->setFilesDispersion(false);
                                $file = $uploader->save($savePath);

                                if (!empty($file)) {
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
                                        $this->mediaDirectory->delete($mediaFolder . '/' . $file['file']);
                                        throw new \RuntimeException("The file is too big.");
                                    } else {
                                        $imgExtens = ["gif", "jpeg", "jpg", "png"];
                                        $temp = explode(".", $file['file']);
                                        $extension = end($temp);

                                        $responseData['status'] = 'ok';
                                        $responseData['path'] = $mediaFolder . "/" . $file['file'];
                                        $responseData['filename'] = $file['file'];
                                        $responseData['fileurl'] = $mediaUrl . $mediaFolder . '/' . $file['file'];
                                        $responseData['filesize'] = $file['size'];

                                        if (in_array($extension, $imgExtens)) {
                                            $responseData['isimage'] = true;
                                        }
                                    }
                                }
                                break;
                            }
                        }
                    }
                } else {
                    throw new \RuntimeException(__('Invalid form profile.'));
                }

            } else {
                throw new \RuntimeException(__('Invalid form Id or field id or empty FILES.'));
            }

            return $this->getResponse()->representJson(
                $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($responseData)
            );

        } catch (\Exception $e) {
            @http_response_code(400);
            $responseData = ['status' => 'error', 'message' => $e->getMessage()];
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->getResponse()->representJson(
                $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($responseData)
            );
        }
    }

    /**
     * @param $path
     * @param $name
     * @return bool|string
     */
    protected function mkdir($path, $name): bool|string
    {
        $path = $path . DIRECTORY_SEPARATOR . $name;

        if (@mkdir($path)) {
            @chmod($path, 0777);
            return $path;
        }

        return false;
    }
}
