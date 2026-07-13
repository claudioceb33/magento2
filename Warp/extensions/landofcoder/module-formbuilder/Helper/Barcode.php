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

use Lof\Formbuilder\Model\Message;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;

class Barcode extends AbstractHelper
{
    public const BARCODE = 'barcode/';
    public const BARCODELABEL = 'barcode_label/';

    /**
     * @var mixed
     */
    protected mixed $barcodeGeneratorPNG = null;

    /**
     * @var Data
     */
    protected Data $helperData;

    /**
     * @var UploaderFactory
     */
    protected UploaderFactory $fileUploaderFactory;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * Data constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Data $helperData
     * @param UploaderFactory $fileUploaderFactory
     * @param string $barcodeGeneratorPNGClass
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Data $helperData,
        UploaderFactory $fileUploaderFactory,
        string $barcodeGeneratorPNGClass = "\Picqer\Barcode\BarcodeGeneratorPNG"
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;

        $this->helperData = $helperData;
        $this->fileUploaderFactory         = $fileUploaderFactory;
        if ($barcodeGeneratorPNGClass && class_exists($barcodeGeneratorPNGClass)) {
            $this->barcodeGeneratorPNG = ObjectManager::getInstance()
            ->create($barcodeGeneratorPNGClass);
        }
    }
    /**
     * @param $code
     * @param string|null $type
     * @return string
     */
    public function getBase64Barcode($code, string $type = null): string
    {
        if ($this->barcodeGeneratorPNG) {
            $type = $type ? $type : $this->barcodeGeneratorPNG::TYPE_CODE_128;
            return base64_encode($this->barcodeGeneratorPNG->getBarcode($code, $type));
        } else {
            return "";
        }
    }

    /**
     * @param string|Message $message
     * @param bool $returnImageHtml
     * @return string
     * @throws NoSuchEntityException
     */
    public function generateBarcodeLabel(Message|string $message, bool $returnImageHtml = true): string
    {
        if (!$this->helperData->getConfig("message_setting/enabled_barcode")) {
            return "";
        }
        if ($message) {
            if (is_object($message)) {
                $code = $message->getQrcode();
            } else {
                $code = $message;
            }
            if ($code) {
                $barcodeBase64 = $this->getBase64Barcode($code);
                $filePath = $this->helperData->saveImageFile($barcodeBase64, $code);
                if ($filePath) {
                    $target = $this->storeManager->getStore()->
                    getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
                    $filePath = $target . $filePath;
                } else {
                    $filePath = 'data:image/png;base64,' . $barcodeBase64;
                }
                return $returnImageHtml ? ("<img width = '100%' src='" . $filePath . '\'>') : $filePath;
            }
        }
        return "";
    }
}
