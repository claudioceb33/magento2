<?php

namespace Swissup\RichSnippets\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Store\Model\Store;

class ProductSettingsToSubsections implements DataPatchInterface, PatchVersionInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $configTable = $this->moduleDataSetup->getTable('core_config_data');
        $select = $connection->select()
            ->from($configTable)
            ->where('path LIKE ?', 'richsnippets/product/%');
        foreach ($connection->fetchAssoc($select) as $configId => $row) {
            $count = 0;
            $newPath = str_replace(
                [
                    'richsnippets/product/condition_',
                    'richsnippets/product/brand_',
                    'richsnippets/product/price_valid_',
                ],
                [
                    'richsnippets/product/condition/',
                    'richsnippets/product/brand/',
                    'richsnippets/product/price_valid/',
                ],
                $row['path'],
                $count
            );
            if ($count > 0) {
                $connection->update(
                    $configTable,
                    ['path' => $newPath],
                    "config_id = {$configId}"
                );
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.6.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
