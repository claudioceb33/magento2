<?php
namespace Ceb\ImportCustomAttribute\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\State;
use Magento\Framework\Module\Dir\Reader as DirReader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;

class ImportTableData implements DataPatchInterface
{
    protected $moduleName = 'Ceb_ImportCustomAttribute';

    protected $fileNameMageplazamethods  = 'Setup/file/table_custom.csv';

    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @var State
     */
    protected $state;

    /**
     * Array of unique table rate keys to protect from duplicates
     *
     * @var array
     */
    protected $importUniqueHash = [];

    /**
     * @var DirReader
     */
    protected $moduleDirReader;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $setup
     * @param State $state
     * @param DirReader $moduleDirReader
     * @param DirectoryList $directoryList
     * @param Filesystem $filesystem
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        State $state,
        DirReader $moduleDirReader,
        DirectoryList $directoryList,
        Filesystem $filesystem
    ) {
        $this->setup = $setup;
        $this->state = $state;
        /*$this->state->setAreaCode('adminhtml');*/
        $this->moduleDirReader = $moduleDirReader;
        $this->directoryList = $directoryList;
        $this->filesystem = $filesystem;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->importMageplazaMethodsCsv();
    }

    public function importMageplazaMethodsCsv()
    {
        $this->importUniqueHash = [];
        $fileName = $this->fileNameMageplazamethods;
        $moduleName = $this->moduleName;

        $columns = [
            'method_id',
            'name',
            'description',
            'status',
            'calculate_rule',
            'image',
            'store_id',
            'customer_group',
            'labels',
            'comments'
        ];

        $fullPath = $this->moduleDirReader->getModuleDir('', $moduleName) . DIRECTORY_SEPARATOR . $fileName;
        $directory = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);
        $stream = $directory->openFile($fullPath);

        $importData = [];

        $adapter = $this->setup->getConnection();
        $adapter->beginTransaction();

        $this->setup->getConnection()->startSetup();

        while (false !== ($csvLine = $stream->readCsv()))
        {
            if (empty($csvLine)) continue;
            $row = $this->getImportRowTable($csvLine);
            if ($row !== false) {
                $importData[] = $row;
            }
        }

        if (count($importData) > 0) {
            $this->saveImportData('table_name', $columns, $importData);
        }

        $this->setup->getConnection()->endSetup();
        $stream->close();

        $adapter->commit();
    }

    public function getImportRowTable($row)
    {
        foreach ($row as $k => $v) {
            $row[$k] = trim($v ?? "");
        }

        $methodId = $row[0];
        $name = $row[1];
        $description = $row[2];
        $status = $row[3];
        $calculateRule = $this->_parseDecimalValue($row[4]);
        $image = $row[5];
        $storeId = $row[6];
        $customerGroup = $row[7];
        $labels = $row[8];
        $comments = $row[9];

        if (isset($this->importUniqueHash[$methodId])) {
            return false;
        }
        $this->importUniqueHash[$methodId] = true;

        return [
            $methodId,
            $name,
            $description,
            $status,
            $calculateRule,
            $image,
            $storeId,
            $customerGroup,
            $labels,
            $comments
        ];
    }

    public function saveImportData($tableName, array $columns, array $data)
    {
        if (!empty($data)) {
            $this->setup->getConnection()->insertArray($this->setup->getTable($tableName), $columns, $data);
        }

        return $this;
    }

    protected function _parseDecimalValue($value)
    {
        if (!is_numeric($value)) {
            return false;
        }
        $value = (double)sprintf('%.4F', $value);
        if ($value < 0.0000) {
            return false;
        }
        return $value;
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
