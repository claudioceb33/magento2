<?php

namespace Demo66\Core\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;

class CreateDefaultInventory implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var SourceInterfaceFactory
     */
    private $sourceInterfaceFactory;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var AdapterInterface
     */
    private $connection;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        SourceInterfaceFactory $sourceInterfaceFactory,
        SourceRepositoryInterface $sourceRepository,
        StockRepositoryInterface $stockRepository,
        DataObjectHelper $dataObjectHelper,
        ResourceConnection $resource
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->sourceInterfaceFactory = $sourceInterfaceFactory;
        $this->sourceRepository = $sourceRepository;
        $this->stockRepository = $stockRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->connection = $resource->getConnection();
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $sourceCode = 'default'; // Replace with your source code
        $stockName = 'Default Stock'; // Replace with your stock name

        try {
            // Check if the source already exists
            $sourceExists = $this->connection->fetchOne(
                $this->connection->select()
                    ->from('inventory_source')
                    ->where('source_code = ?', $sourceCode)
            );

            if (!$sourceExists) {
                // Source doesn't exist, insert a new one
                $this->connection->insert(
                    'inventory_source',
                    [
                        'source_code' => $sourceCode,
                        'name' => 'Default',
                        // Add other source data as needed
                    ]
                );
            }

            // Check if the stock already exists
            $stockExists = $this->connection->fetchOne(
                $this->connection->select()
                    ->from('inventory_stock')
                    ->where('name = ?', $stockName)
            );

            if (!$stockExists) {
                // Stock doesn't exist, insert a new one
                $this->connection->insert(
                    'inventory_stock',
                    [
                        'name' => $stockName,
                        // Add other stock data as needed
                    ]
                );
            }

            $stockId = 1;/*$this->connection->fetchOne(
                $this->connection->select()
                    ->from('inventory_stock')
                    ->where('name = ?', $stockName)
                    ->columns('stock_id')
            );*/

            $linkExists = $this->connection->fetchOne(
                $this->connection->select()
                    ->from('inventory_source_stock_link')
                    ->where('source_code = ?', $sourceCode)
                    ->where('stock_id = ?', $stockId)
            );

            if (!$linkExists) {
                // Link doesn't exist, insert a new one
                $this->connection->insert(
                    'inventory_source_stock_link',
                    [
                        'source_code' => $sourceCode,
                        'stock_id' => $stockId,
                        // Add other link data as needed
                    ]
                );
                $this->connection->insert(
                    'inventory_stock_sales_channel',
                    [
                        'type' => 'website',
                        'code' => 'demo66',
                        'stock_id' => $stockId,
                        // Add other link data as needed
                    ]
                );
            }

            // 'Default inventory source and stock created successfully.' . PHP_EOL;
        } catch (\Exception $e) {
            // 'An error occurred while creating default inventory source and stock.' . PHP_EOL;
            $e->getMessage();
        }

        $this->moduleDataSetup->endSetup();
    }


    /**
     * {@inheritdoc}
     */
    public function revert()
    {
        $this->moduleDataSetup->startSetup();

        // You can implement revert logic here if needed

        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }
}
