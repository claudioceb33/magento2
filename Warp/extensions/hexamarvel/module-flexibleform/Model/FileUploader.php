<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */
namespace Hexamarvel\FlexibleForm\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;

class FileUploader
{
    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        ManagerInterface $messageManager
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem      = $filesystem;
        $this->messageManager  = $messageManager;
    }

    /**
     * Save files
     *
     * @param string $fileId
     * @return string
     * @throws LocalizedException
     */
    public function uploadFiles($fileId, $allowedTypes)
    {
        try {
            $uploaderFactory = $this->uploaderFactory->create(['fileId' => $fileId]);
            $uploaderFactory->setAllowedExtensions($allowedTypes);
            $uploaderFactory->setAllowRenameFiles(true);
            $uploaderFactory->setFilesDispersion(true);
            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $destinationPath = $mediaDirectory->getAbsolutePath('hexamarvel/flexibleform');
            $result = $uploaderFactory->save($destinationPath);
            if ($result['file']) {
                $result = 'hexamarvel/flexibleform'.$result['file'];
                return $result;
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return false;
    }
}
