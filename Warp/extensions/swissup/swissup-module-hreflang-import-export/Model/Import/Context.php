<?php

namespace Swissup\HreflangImportExport\Model\Import;

use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\ImportExport\Helper\Data as ImportHelper;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Model\ResourceModel\Import\Data;

class Context
{
    private Data $importData;
    private Helper $resourceHelper;
    private ImportHelper $importExportData;
    private JsonHelper $jsonHelper;
    private ProcessingErrorAggregatorInterface $errorAggregator;
    public function __construct(
        Data $importData,
        Helper $resourceHelper,
        ImportHelper $importExportData,
        JsonHelper $jsonHelper,
        ProcessingErrorAggregatorInterface $errorAggregator
    ) {
        $this->importData = $importData;
        $this->resourceHelper = $resourceHelper;
        $this->importExportData = $importExportData;
        $this->jsonHelper = $jsonHelper;
        $this->errorAggregator = $errorAggregator;
    }

    public function getImportData(): Data
    {
        return $this->importData;
    }

    public function getResourceHelper(): Helper
    {
        return $this->resourceHelper;
    }

    public function getImportExportData(): ImportHelper
    {
        return $this->importExportData;
    }

    public function getJsonHelper(): JsonHelper
    {
        return $this->jsonHelper;
    }

    public function getErrorAggregator(): ProcessingErrorAggregatorInterface
    {
        return $this->errorAggregator;
    }
}
