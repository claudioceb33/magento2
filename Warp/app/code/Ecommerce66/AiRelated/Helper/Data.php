<?php
namespace Ecommerce66\AiRelated\Helper;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Filesystem\DirectoryList;
use Psr\Log\LoggerInterface;

class Data
{
    private $scopeConfig;
    private $curl;
    private $logger;
    private $directoryList;
    private $aicoreHelper;
    private $fileDriver;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Curl $curl,
        LoggerInterface $logger,
        DirectoryList $directoryList,
    \Ecommerce66\AiCore\Helper\Data $aicoreHelper,
        \Magento\Framework\Filesystem\Driver\File $fileDriver
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->curl = $curl;
        $this->logger = $logger;
        $this->directoryList = $directoryList;
        $this->aicoreHelper = $aicoreHelper;
        $this->fileDriver = $fileDriver;
    }

    public function isEnabled($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue('ecommerce66_ai/ai_related/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getDefaultLimit($storeId = null)
    {
        $v = $this->scopeConfig->getValue('ecommerce66_ai/ai_related/default_limit', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        return $v ? (int)$v : 10;
    }

    public function getCacheTtl($storeId = null)
    {
        $v = $this->scopeConfig->getValue('ecommerce66_ai/ai_related/cache_ttl', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        return $v ? (int)$v : 1440; // minutes
    }

    public function isInfoLoggingEnabled($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue('ecommerce66_ai/ai_related/enable_info_log', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    private function getCachePathForSku($sku)
    {
        $dir = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR) . '/cache/ai_related';
        try {
            if (!$this->fileDriver->isDirectory($dir)) {
                $this->fileDriver->createDirectory($dir);
            }
        } catch (\Throwable $e) {
            // log directory creation failure and continue; writing will fail later if necessary
            if ($this->logger) {
                $this->logger->warning('AI Related: could not create cache directory ' . $dir . ': ' . $e->getMessage());
            }
        }
        return $dir . '/' . hash('sha256', (string)$sku) . '.json';
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getCached($sku)
    {
        $path = $this->getCachePathForSku($sku);
        $data = $this->readCacheFile($path);
        if (!$data || !isset($data['ts']) || !isset($data['payload'])) {
            return null;
        }
        $ttl = $this->getCacheTtl() * 60;
        if (time() - $data['ts'] > $ttl) {
            $this->deleteFileSafe($path);
            return null;
        }
        return $data['payload'];
    }

    /**
     * Read and decode cache file, returning array or null.
     */
    private function readCacheFile(string $path): ?array
    {
        try {
            if (!$this->fileDriver->isExists($path)) {
                return null;
            }
            $raw = $this->fileDriver->fileGetContents($path);
            $data = json_decode($raw, true);
            return is_array($data) ? $data : null;
        } catch (\Throwable $e) {
            if ($this->logger) {
                $this->logger->debug('AI Related: error reading cache ' . $path . ': ' . $e->getMessage());
            }
            return null;
        }
    }

    /**
     * Delete file safely and log failures at debug level.
     */
    private function deleteFileSafe(string $path): void
    {
        try {
            if ($this->fileDriver->isExists($path)) {
                $this->fileDriver->deleteFile($path);
            }
        } catch (\Throwable $e) {
            if ($this->logger) {
                $this->logger->debug('AI Related: failed deleting file ' . $path . ': ' . $e->getMessage());
            }
        }
    }

    public function setCached($sku, $payload)
    {
        $path = $this->getCachePathForSku($sku);
        $data = ['ts' => time(), 'payload' => $payload];
        try {
            $this->fileDriver->filePutContents($path, json_encode($data));
        } catch (\Throwable $e) {
            if ($this->logger) {
                $this->logger->debug('AI Related: failed to write cache for ' . $path . ': ' . $e->getMessage());
            }
        }
    }

    public function fetchRelatedFromAi($sku, $limit = null)
    {
        try {
            $base = rtrim($this->aicoreHelper->getBaseUrl(), '/');
            $limit = $limit ?: $this->getDefaultLimit();
            $url = $base . '/api/v1/recommendations/' . urlencode($sku) . '/related?limit=' . (int)$limit;

            $this->writeLog('info', "AI Related: Requesting $url");

            $apiKey = $this->aicoreHelper->getApiKey();
            $this->curl->setHeaders(['X-API-Key' => $apiKey]);
            $this->curl->get($url);
            $status = $this->curl->getStatus();
            $body = $this->curl->getBody();
            $this->writeLog('info', "AI Related: response status $status body: " . substr($body, 0, 1000));

            if ($status !== 200) {
                return null;
            }
            $json = json_decode($body, true);
            if (!is_array($json)) {
                return null;
            }
            $this->setCached($sku, $json);
            return $json;
        } catch (\Throwable $e) {
            $this->writeLog('error', 'AI Related: fetch error: ' . $e->getMessage());
            return null;
        }
    }

    private function writeLog($level, $message)
    {
        try {
            $logDir = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR) . '/log';
            try {
                if (!$this->fileDriver->isDirectory($logDir)) {
                    $this->fileDriver->createDirectory($logDir);
                }
            } catch (\Throwable $e) {
                if ($this->logger) {
                    $this->logger->debug('AI Related: could not create log directory ' . $logDir . ': ' . $e->getMessage());
                }
            }
            $file = $logDir . '/ai_related.log';
            $line = '[' . date('Y-m-d H:i:s') . '] ' . strtoupper($level) . ': ' . $message . PHP_EOL;
            $this->fileDriver->filePutContents($file, $line, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            // fallback to generic logger
            if ($this->logger) {
                $this->logger->error('AI Related logging failed: ' . $e->getMessage());
            }
        }
    }

    public function log($level, $message)
    {
        $this->writeLog($level, $message);
    }
}
