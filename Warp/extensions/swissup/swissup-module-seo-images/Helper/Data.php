<?php

namespace Swissup\SeoImages\Helper;

use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\AlreadyExistsException;
use Swissup\SeoImages\Model;

class Data extends AbstractHelper
{
    const XML_PATH_ENABLED = 'seo_images/general/enabled';
    const XML_PATH_CLEAR_PARAMS = 'seo_images/misc_string/clear_params';
    const XML_PATH_PRODUCT_TEMP = 'seo_images/image_name/product_image';
    const XML_PATH_PRODUCTION_M = 'seo_images/production/enabled';

    /**
     * @var array
     */
    private $cachedImages = [];

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var Model\EntityFactory
     */
    protected $seoImageFactory;

    /**
     * @var Model\ResourceModel\Entity\CollectionFactory
     */
    protected $seoImageCollectionFactory;

    /**
     * @param \Magento\Framework\App\State                 $appState
     * @param Model\EntityFactory                          $seoImageFactory
     * @param Model\ResourceModel\Entity\CollectionFactory $seoImageCollectionFactory
     * @param Context                                      $context
     */
    public function __construct(
        \Magento\Framework\App\State $appState,
        Model\EntityFactory $seoImageFactory,
        Model\ResourceModel\Entity\CollectionFactory $seoImageCollectionFactory,
        Context $context
    ) {
        $this->appState = $appState;
        $this->seoImageFactory = $seoImageFactory;
        $this->seoImageCollectionFactory = $seoImageCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Inspired by and bounded with \Magento\MediaStorage\App\Media::getOriginalImage
     * Works with path as well as with url.
     *
     * @param  string $path
     * @return string
     */
    public function buildFileKey($path) {
        // Remove get parameters string when url suplied.
        list($path) = explode('?', $path);

        return preg_replace('|^.*((?:/[^/]+){3})$|', '$1', $path);
    }

    /**
     * Save SEO name for image when it is not empty
     *
     * @param  string $fileKey
     * @param  string $originalFile
     * @param  string $targetFile
     * @return $this
     */
    public function saveSeoImage($fileKey, $originalFile, $targetFile)
    {
        if ($targetFile) {
            $seoImage = $this->getSeoImage('file_key', $fileKey);

            $seoImage->setFileKey($fileKey)
                ->setOriginalFile($originalFile)
                ->setTargetFile($targetFile);
            try {
                $seoImage->save();
            } catch (\Zend_Db_Statement_Exception | AlreadyExistsException $e) {
                // Exception is 'SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry...'
                // Exception is 'Unique constraint violation found'
                // Someone was faster than you.
            }
        }

        return $this;
    }

    /**
     * Get template for name if image
     *
     * @return string
     */
    public function getSeoImageNameTemplate()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PRODUCT_TEMP);
    }

    /**
     * Is module enabled in Stores - Conguration
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED);
    }

    /**
     * Is replace hash with clear params enabled.
     *
     * @return boolean
     */
    public function isClearParams()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CLEAR_PARAMS);
    }

    /**
     * Can change image name? Module enabled and template is not empty.
     *
     * @return boolean
     */
    public function canChangeName()
    {
        return $this->isEnabled() && $this->getSeoImageNameTemplate();
    }

    public function getCachedSeoImage($originalFile)
    {
        return $this->seoImageFactory->create()
            ->load($originalFile, 'original_file');
    }

    public function isProduction()
    {
        if ($this->appState->getAreaCode() === Area::AREA_GLOBAL) {
            // Force disable production when CLI command executed.
            // Its for `catalog:images:resize` command.
            return false;
        }

        return $this->scopeConfig->isSetFlag(self::XML_PATH_PRODUCTION_M);
    }

    /**
     * @param  string[]  $originalFiles
     * @return void
     */
    public function preloadImages(array $originalFiles)
    {
        foreach ($originalFiles as &$file) {
            // All fields in table has MAX_LENGTH = 255.
            $file = substr($file, 0, 255);
        }

        // Remove already cached images from requested
        foreach ($this->cachedImages as $image) {
            $key = array_search($image->getOriginalFile(), $originalFiles);
            if ($key !== false) {
                unset($originalFiles[$key]);
            }

            if (empty($originalFiles)) {
                break;
            }
        }

        if (empty($originalFiles)) {
            return;
        }

        $collection = $this->seoImageCollectionFactory->create();
        $collection->addFieldToFilter('original_file', ['in' => $originalFiles]);
        foreach ($collection as $image) {
            // Mark object as not modified to prevent save into DB when no data changed.
            $image->setHasDataChanges(false);
            $this->cachedImages[$image->getFileKey()] = $image;
        }
    }

    /**
     * Get SEO Image model
     *
     * @param  string $field
     * @param  string $value
     * @return Model\Entity
     */
    public function getSeoImage($field, $value)
    {
        // All fields in table has MAX_LENGTH = 255.
        $value = substr($value, 0, 255);
        $cachedImage = null;
        if ($field === 'file_key') {
            $cachedImage = $this->cachedImages[$value] ?? null;
        } elseif (isset($this->cachedImages)) {
            foreach ($this->cachedImages as $image) {
                if ($image->getData($field) == $value) {
                    $cachedImage = $image;
                    break;
                }
            }
        }

        if (!$cachedImage) {
            $image = $this->seoImageFactory->create()->load($value, $field);
            if ($image->getFileKey()) {
                $this->cachedImages[$image->getFileKey()] = $image;
            }

            return $image;
        }

        return $cachedImage;
    }

    public function getRequest()
    {
        return $this->_getRequest();
    }
}
