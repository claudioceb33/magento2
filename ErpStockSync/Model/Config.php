<?php

declare(strict_types=1);

namespace Ceb\ErpStockSync\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    private const XML_PATH_ENABLED =
        'ceb_erp_stock_sync/general/enabled';

    private const XML_PATH_MODE =
        'ceb_erp_stock_sync/general/mode';

    private const XML_PATH_DRY_RUN =
        'ceb_erp_stock_sync/general/dry_run';

    private const XML_PATH_ERP_PAGE_SIZE =
        'ceb_erp_stock_sync/processing/erp_page_size';

    private const XML_PATH_BATCH_SIZE =
        'ceb_erp_stock_sync/processing/batch_size';

    private const XML_PATH_REQUEST_TIMEOUT =
        'ceb_erp_stock_sync/processing/request_timeout';

    private const XML_PATH_MAX_RETRIES =
        'ceb_erp_stock_sync/processing/max_retries';

    private const XML_PATH_RETRY_DELAY =
        'ceb_erp_stock_sync/processing/retry_delay';

    private const XML_PATH_SOURCE_CODE =
        'ceb_erp_stock_sync/inventory/source_code';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED
        );
    }

    public function getMode(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_MODE
        );
    }

    public function isDryRun(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DRY_RUN
        );
    }

    public function getErpPageSize(): int
    {
        return max(
            1,
            (int)$this->scopeConfig->getValue(
                self::XML_PATH_ERP_PAGE_SIZE
            )
        );
    }

    public function getBatchSize(): int
    {
        return max(
            1,
            (int)$this->scopeConfig->getValue(
                self::XML_PATH_BATCH_SIZE
            )
        );
    }

    public function getRequestTimeout(): int
    {
        return max(
            1,
            (int)$this->scopeConfig->getValue(
                self::XML_PATH_REQUEST_TIMEOUT
            )
        );
    }

    public function getMaxRetries(): int
    {
        return max(
            0,
            (int)$this->scopeConfig->getValue(
                self::XML_PATH_MAX_RETRIES
            )
        );
    }

    public function getRetryDelay(): int
    {
        return max(
            0,
            (int)$this->scopeConfig->getValue(
                self::XML_PATH_RETRY_DELAY
            )
        );
    }

    public function getSourceCode(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_SOURCE_CODE
        );
    }
}
