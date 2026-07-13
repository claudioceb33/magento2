<?php

namespace Swissup\SeoImages\Plugin\Model\View\Asset;

use Magento\Framework\Filesystem;
use Magento\Framework\Image\Factory as ImageFactory;
use Magento\Catalog\Model\Product\Media\ConfigInterface as MediaConfig;
use Swissup\SeoImages\Helper\Data as Helper;
use Swissup\SeoImages\Model\FileResolver;

class Image
{
    /**
     * @var FileResolver
     */
    private $fileResolver;

    /**
     * @var Filesystem\Io\File
     */
    private $filesystemIo;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var ImageFactory
     */
    private $imageFactory;

    /**
     * @var MediaConfig
     */
    private $mediaConfig;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * @param Filesystem         $filesystem
     * @param Filesystem\Io\File $filesystemIo
     * @param FileResolver       $fileResolver
     * @param Helper             $helper
     * @param ImageFactory       $imageFactory
     * @param MediaConfig        $mediaConfig
     */
    public function __construct(
        Filesystem $filesystem,
        Filesystem\Io\File $filesystemIo,
        FileResolver $fileResolver,
        Helper $helper,
        ImageFactory $imageFactory,
        MediaConfig $mediaConfig
    ) {
        $this->fileResolver = $fileResolver;
        $this->filesystemIo = $filesystemIo;
        $this->helper = $helper;
        $this->imageFactory = $imageFactory;
        $this->mediaConfig = $mediaConfig;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        );
    }

    /**
     * After plugin.
     * Change destination path to resized image on server.
     *
     * @param  \Magento\Catalog\Model\View\Asset\Image $subject
     * @param  string                                  $result
     * @return string
     */
    public function afterGetPath(
        \Magento\Catalog\Model\View\Asset\Image $subject,
        $result
    ) {
        if (!$this->helper->canChangeName()) {
            return $result;
        }

        $originalFile = $subject->getFilePath();
        $targetFile = $this->fileResolver->getTargetFile($originalFile);
        if (!$targetFile) {
            return $result;
        }

        $newPath = str_replace($originalFile, $targetFile, $result);
        if (!$this->helper->isProduction()) {
            $fileKey = $this->helper->buildFileKey($newPath);
            $this->helper->saveSeoImage($fileKey, $originalFile, $targetFile);
        }

        return $newPath;
    }

    /**
     * Aftre plugin.
     * Change URL to resized image.
     *
     * @param  \Magento\Catalog\Model\View\Asset\Image $subject
     * @param  string                                  $result
     * @return string
     */
    public function afterGetUrl(
        \Magento\Catalog\Model\View\Asset\Image $subject,
        $result
    ) {
        if (!$this->helper->canChangeName()) {
            return $result;
        }

        $url = $result;
        $params = parse_url($url, PHP_URL_QUERY);
        $isCatalogMediaCache = $this->isCatalogMediaCache($url, $subject->getModule());
        if (!$params && !$isCatalogMediaCache) {
            // looks like direct url to media file provided
            // do nothing

            return $url;
        }

        $originalFile = $subject->getFilePath();
        $targetFile = $this->fileResolver->getTargetFile($originalFile);
        if (!$targetFile) {
            return $url;
        }

        if (!$params && $isCatalogMediaCache) {
            // option "Unique hash per image variant (Legacy mode)" enabled
            $newUrl = str_replace($originalFile, $targetFile, $url);
        }

        if ($params && !$isCatalogMediaCache) {
            // option "Image optimization based on query parameters" enabled
            // copy original file to catalog media cache so optimizer could access it
            $cacheFile =  DIRECTORY_SEPARATOR . $subject->getModule() . $targetFile;
            $this->saveToCatalogMediaStorageCache($originalFile, $cacheFile);
            $newUrl = str_replace($originalFile, $cacheFile, $url);
        }

        if (!$this->helper->isProduction()) {
            $fileKey = $this->helper->buildFileKey($newUrl);
            $this->helper->saveSeoImage($fileKey, $originalFile, $targetFile);
        }

        return $newUrl;
    }

    /**
     * Check if image url is from catalog media cache
     */
    private function isCatalogMediaCache(string $url, string $cacheDir): string
    {
        return strpos($url, DIRECTORY_SEPARATOR . $cacheDir . DIRECTORY_SEPARATOR) !== false;
    }

    /**
     * Save original image in catalog media cache
     */
    private function saveToCatalogMediaStorageCache(
        string $source,
        string $destination
    ): void {
        $mediastoragefilename = $this->mediaConfig->getMediaPath($source);
        $sourcePath = $this->mediaDirectory->getAbsolutePath($mediastoragefilename);
        $destinationPath = str_replace($source, $destination, $sourcePath);
        if (!$this->filesystemIo->fileExists($destinationPath)) {
            $this->imageFactory->create($sourcePath)->save($destinationPath);
        }
    }
}
