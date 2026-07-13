<?php
namespace Ecommerce66\DbClean\Cron;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;

class LogTables
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * LogTables constructor.
     *
     * @param LoggerInterface      $logger
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceConnection   $resource
     */
    public function __construct(
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        ResourceConnection $resource
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->resource = $resource;
    }

    /**
     *
     */
    public function execute()
    {
        $cronEnabled = (int)$this->scopeConfig->getValue(
            'dbclean/log_tables/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (!$cronEnabled) {
            $this->logger->info('Database cleaning cron is disabled.');
            return;
        }

        $retentionDays = (int)$this->scopeConfig->getValue(
            'dbclean/log_tables/retention_days',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        /**
         * Proccess log tables
         */
        $logTables = $this->scopeConfig->getValue(
            'dbclean/log_tables/to_clean',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $logTables = explode(',',$logTables);

        $connection = $this->resource->getConnection();

        foreach ($logTables as $logTable) {
            $tableName = $this->resource->getTableName($logTable);
            $dateLimit = date('Y-m-d H:i:s', strtotime("-{$retentionDays} days"));

            try {
                if ($connection->isTableExists($tableName)) {
                    $connection->beginTransaction();
                    $connection->delete($tableName, ['created_at < ?' => $dateLimit]);
                    $connection->commit();
                    $this->logger->info("DbClean: {$tableName} deleted records older than {$retentionDays} days.");
                }
            } catch (\Exception $e) {
                $connection->rollBack();
                $this->logger->error('Error during database cleaning: ' . $e->getMessage());
            }
        }

        /**
         * Proccess tables to truncate
         */
        $truncateTables = $this->scopeConfig->getValue(
            'dbclean/log_tables/to_truncate',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $truncateTables = explode(',',$truncateTables);

        foreach ($truncateTables as $truncateTable) {
            $tableName = $this->resource->getTableName($truncateTable);

            try {
                if ($connection->isTableExists($tableName)) {
                    $this->resource->getConnection()->truncateTable($tableName);
                    $this->logger->info('DbClean: ' . $tableName . 'truncated successfully.');
                }
            } catch (\Exception $e) {
                // Log error
                $this->logger->error('Error truncating table ' . $tableName . ': ' . $e->getMessage());
            }
        }
    }
}
