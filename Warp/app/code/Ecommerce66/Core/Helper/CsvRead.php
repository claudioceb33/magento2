<?php
namespace Ecommerce66\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Module\Dir\Reader as DirReader;
use Magento\Framework\Filesystem\DirectoryList;

class CsvRead extends AbstractHelper
{
    /**
     * @var DriverInterface
     */
    protected $driver;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var DirReader
     */
    protected $moduleDirReader;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * CsvRead constructor.
     *
     * @param Context         $context
     * @param DriverInterface $driver
     * @param File            $file
     * @param DirReader       $moduleDirReader
     * @param DirectoryList   $directoryList
     */
    public function __construct(
        Context $context,
        DriverInterface $driver,
        File $file,
        DirReader $moduleDirReader,
        DirectoryList $directoryList
    ) {
        $this->context = $context;
        $this->driver = $driver;
        $this->file = $file;
        $this->moduleDirReader = $moduleDirReader;
        $this->directoryList = $directoryList;
        parent::__construct($context);
    }

    /**
     * Read csv file and return data as array
     *
     * @param string $filePath
     * @param string $delimiter
     * @return array|bool
     */
    public function readCsv($filePath, $delimiter = ',', $moduleIdentifier = null)
    {
        $basePath = empty($moduleIdentifier)
                    ? $this->directoryList->getRoot()
                    : $this->moduleDirReader->getModuleDir('', $moduleIdentifier);
        $fullPath = $basePath . DIRECTORY_SEPARATOR . $filePath;

        if (!$this->driver->isExists($fullPath)) {
            return false;
        }

        $csvData = [];
        $fileHandler = $this->driver->fileOpen($fullPath, 'r');
        $headers = $this->driver->fileGetCsv($fileHandler, null, $delimiter);
        if (!empty($headers)) {
            while ($rowData = $this->driver->fileGetCsv($fileHandler, null, $delimiter)) {
                if (!empty($rowData) && !empty($rowData[0])) {
                    $csvData[] = array_combine($headers, $rowData);
                }
            }
        }
        $this->driver->fileClose($fileHandler);

        return $csvData;
    }
}
