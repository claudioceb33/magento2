<?php
namespace Ecommerce66\AiRelated\Model;

use Psr\Log\LoggerInterface;
use Ecommerce66\AiRelated\Helper\Data as AiHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\ProductLinkManagementInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Framework\App\State as AppState;
use Magento\Framework\App\Area;
use Magento\Framework\App\CacheInterface as CacheFrontend;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface as ConfigWriter;

class Generator
{
    private const CACHE_KEY = 'ecommerce66_ai_ai_related_last_entity_id';
    private $logger;
    private $aiHelper;
    private $productRepository;
    private $productCollectionFactory;
    private $productLinkManagement;
    private $productLinkFactory;
    private $appState;
    private $cacheFrontend;
    private $scopeConfig;
    private $configWriter;
    /**
     * In-memory last persisted entity id to avoid reading stale config within the same process
     * @var int|null
     */
    private $lastPersistedId = null;

    public function __construct(
        LoggerInterface $logger,
        AiHelper $aiHelper,
        ProductRepositoryInterface $productRepository,
        ProductCollectionFactory $productCollectionFactory,
        ProductLinkManagementInterface $productLinkManagement,
        ProductLinkInterfaceFactory $productLinkFactory
        ,AppState $appState
        ,CacheFrontend $cacheFrontend
        ,ScopeConfigInterface $scopeConfig
        ,ConfigWriter $configWriter
    ) {
        $this->logger = $logger;
        $this->aiHelper = $aiHelper;
        $this->productRepository = $productRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productLinkManagement = $productLinkManagement;
        $this->productLinkFactory = $productLinkFactory;
        $this->appState = $appState;
        $this->cacheFrontend = $cacheFrontend;
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
    }

    /**
     * Process a single SKU without forcing replacements.
     * Returns array with processed/saved/skipped counts for this SKU.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function processSkuNonForce(string $sku): array
    {
        $processed = 0;
        $saved = 0;
        $skipped = 0;
        // check existing related links (skip if not forcing)
        if ($this->hasRelatedLinks($sku)) {
            $skipped++;
            return ['processed' => $processed, 'saved' => $saved, 'skipped' => $skipped];
        }

        $data = $this->fetchAiDataForSku($sku);
        if (empty($data) || !is_array($data)) {
            $processed++;
            return ['processed' => $processed, 'saved' => $saved, 'skipped' => $skipped];
        }

        $productLinks = $this->buildProductLinksForSku($sku, $data);

        if (!empty($productLinks)) {
            // Debug: log the payload and existence of linked SKUs
            $linkedSkus = array_map(function($l){ return method_exists($l, 'getLinkedProductSku') ? $l->getLinkedProductSku() : null; }, $productLinks);
            $this->logger->debug('AI Related: about to setProductLinks for ' . $sku . ' => ' . json_encode($linkedSkus));
            $savedCount = $this->saveProductLinksMerge($sku, $productLinks);
            $saved += $savedCount;
        }
        $processed++;
        return ['processed' => $processed, 'saved' => $saved, 'skipped' => $skipped];
    }
    /** Wrapper for non-force processing */
    private function processSku(string $sku): array
    {
        return $this->processSkuNonForce($sku);
    }

    /**
     * Process a single SKU forcing replacement of related links.
     */
    private function processSkuForce(string $sku): array
    {
        $processed = 0;
        $saved = 0;
        $skipped = 0;
        // when forcing, do not skip even if there are existing related links
        $data = $this->fetchAiDataForSku($sku);
        if (empty($data) || !is_array($data)) {
            $processed++;
            return ['processed' => $processed, 'saved' => $saved, 'skipped' => $skipped];
        }
        $productLinks = $this->buildProductLinksForSku($sku, $data);
        if (!empty($productLinks)) {
            $linkedSkus = array_map(function($l){ return method_exists($l, 'getLinkedProductSku') ? $l->getLinkedProductSku() : null; }, $productLinks);
            $this->logger->debug('AI Related: about to setProductLinks (force) for ' . $sku . ' => ' . json_encode($linkedSkus));
            $savedCount = $this->saveProductLinksReplace($sku, $productLinks);
            $saved += $savedCount;
        }
        $processed++;
        return ['processed' => $processed, 'saved' => $saved, 'skipped' => $skipped];
    }

    private function hasRelatedLinks(string $sku): bool
    {
        try {
            $prod = $this->productRepository->get($sku);
            $links = $prod->getProductLinks();
            foreach ($links as $l) {
                if (method_exists($l, 'getLinkType') && $l->getLinkType() == 'related') {
                    return true;
                }
            }
            return false;
        } catch (\Throwable $e) {
            // if we cannot load product, assume no related links to let generator attempt
            $this->logger->warning('AI Related: could not determine existing links for ' . $sku . ': ' . $e->getMessage());
            return false;
        }
    }

    private function fetchAiDataForSku(string $sku): ?array
    {
        $cached = $this->aiHelper->getCached($sku);
        if (is_array($cached)) {
            return $cached;
        }
        return $this->aiHelper->fetchRelatedFromAi($sku, $this->aiHelper->getDefaultLimit());
    }

    /**
     * Build ProductLink objects from AI data for a given SKU. Skips non-existent SKUs.
     * Returns array of ProductLinkInterface objects.
     */
    private function buildProductLinksForSku(string $sku, array $data): array
    {
        $productLinks = [];
        foreach ($data as $item) {
            if (empty($item['sku'])) continue;
            try {
                $linkedProduct = $this->productRepository->get((string)$item['sku']);
                $linkedType = $linkedProduct->getTypeId() ?: null;
            } catch (\Exception $e) {
                $this->logger->warning('AI Related: recommended SKU not found, skipping: ' . (string)$item['sku']);
                continue;
            }

            $link = $this->productLinkFactory->create();
            $link->setSku($sku);
            $link->setLinkedProductSku((string)$item['sku']);
            $link->setLinkType('related');
            if (!empty($linkedType) && method_exists($link, 'setLinkedProductType')) {
                $link->setLinkedProductType($linkedType);
            }
            if (isset($item['position'])) {
                $link->setPosition((int)$item['position']);
            }
            $productLinks[] = $link;
        }
        return $productLinks;
    }

    /**
     * Save provided product links for a given SKU. Returns number of saved links (0 on failure).
     */
    private function saveProductLinksMerge(string $sku, array $productLinks): int
    {
        try {
            $productToSave = $this->productRepository->get($sku);
            $existingLinks = $productToSave->getProductLinks();
            // merge once per product
            $newLinks = array_merge($existingLinks, $productLinks);
            $productToSave->setProductLinks($newLinks);
            $this->productRepository->save($productToSave);
            if ($this->aiHelper->isInfoLoggingEnabled()) {
                $this->logger->info('AI Related: saved ' . count($productLinks) . ' related links for ' . $sku);
            }
            return count($productLinks);
        } catch (\Throwable $e) {
            $this->logger->error('AI Related: failed to save links for ' . $sku . ': ' . $e->getMessage());
            $this->logger->error('AI Related: exception details: ' . $e->__toString());
            return 0;
        }
    }

    private function saveProductLinksReplace(string $sku, array $productLinks): int
    {
        try {
            $productToSave = $this->productRepository->get($sku);
            $existingLinks = $productToSave->getProductLinks();
            // replace existing "related" links with new ones but keep other link types
            $filtered = [];
            foreach ($existingLinks as $l) {
                if (method_exists($l, 'getLinkType') && $l->getLinkType() === 'related') {
                    continue; // drop existing related links
                }
                $filtered[] = $l;
            }
            $newLinks = array_merge($filtered, $productLinks);
            $productToSave->setProductLinks($newLinks);
            $this->productRepository->save($productToSave);
            if ($this->aiHelper->isInfoLoggingEnabled()) {
                $this->logger->info('AI Related: saved ' . count($productLinks) . ' related links for ' . $sku);
            }
            return count($productLinks);
        } catch (\Throwable $e) {
            $this->logger->error('AI Related: failed to save links for ' . $sku . ': ' . $e->getMessage());
            $this->logger->error('AI Related: exception details: ' . $e->__toString());
            return 0;
        }
    }

    /**
     * Process generation for up to $batch products.
     * Criteria: products that currently have no related links.
     * Returns array with summary info.
     */
    /**
     * Generate related links.
     * If $startEntityId is null, generator will read last processed id from cache and continue from there.
     * If $reset is true, it will start from 0 and overwrite cache.
     */
    /**
     * Generate related links without forcing replacement.
     */
    public function generate(int $batch = 50, ?int $startEntityId = null): array
    {
        return $this->generateInternalNonForce($batch, $startEntityId);
    }

    /**
     * Generate related links forcing replacement of existing related links.
     */
    public function generateForce(int $batch = 50, ?int $startEntityId = null): array
    {
        return $this->generateInternalForce($batch, $startEntityId);
    }

    /**
     * Internal generate implementation (non-force).
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function generateInternalNonForce(int $batch = 50, ?int $startEntityId = null): array
    {
        // Ensure area code is set when running from CLI/cron to avoid "Area code is not set" exceptions
        try {
            $this->appState->getAreaCode();
        } catch (\Throwable $e) {
            try {
                $this->appState->setAreaCode(Area::AREA_CRONTAB);
                $this->logger->debug('AI Related: set area code to crontab');
            } catch (\Throwable $e) {
                // Log area code set failure so static analyzers don't consider this an empty-catch
                $this->logger->warning('AI Related: could not set area code: ' . $e->getMessage());
            }
        }
        $processed = 0;
        $saved = 0;
        $skipped = 0;

        try {
            // determine starting entity id
            $lastId = $this->determineStartEntityId($startEntityId);

            $collection = $this->productCollectionFactory->create();
            $collection->addAttributeToSelect('sku');
            $collection->addAttributeToFilter('entity_id', ['gt' => $lastId]);
            $collection->setPageSize($batch);
            $collection->setOrder('entity_id', 'ASC');

            $maxProcessedId = $lastId;
            foreach ($collection as $product) {
                $sku = (string)$product->getSku();
                try {
                    $result = $this->processSku($sku);
                    $processed += $result['processed'];
                    $saved += $result['saved'];
                    $skipped += $result['skipped'];
                    $prodId = (int)$product->getId();
                    if ($prodId > $maxProcessedId) {
                        $maxProcessedId = $prodId;
                    }
                } catch (\Throwable $e) {
                    $this->logger->error('AI Related: generator error for ' . $sku . ': ' . $e->getMessage());
                    $this->logger->error('AI Related: exception details: ' . $e->__toString());
                }
            }
            // persist last processed id if we processed any
            $this->persistLastProcessedId($maxProcessedId, $lastId);
        } catch (\Throwable $e) {
            $this->logger->error('AI Related: generator fatal: ' . $e->getMessage());
        }

        return ['processed' => $processed, 'saved' => $saved, 'skipped' => $skipped];
    }

    /**
     * Internal generate implementation (force replacement).
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function generateInternalForce(int $batch = 50, ?int $startEntityId = null): array
    {
        // Ensure area code is set when running from CLI/cron to avoid "Area code is not set" exceptions
        try {
            $this->appState->getAreaCode();
        } catch (\Throwable $e) {
            try {
                $this->appState->setAreaCode(Area::AREA_CRONTAB);
                $this->logger->debug('AI Related: set area code to crontab');
            } catch (\Throwable $e) {
                $this->logger->warning('AI Related: could not set area code: ' . $e->getMessage());
            }
        }
        $processed = 0;
        $saved = 0;
        $skipped = 0;

        try {
            // determine starting entity id
            $lastId = $this->determineStartEntityId($startEntityId);

            $collection = $this->productCollectionFactory->create();
            $collection->addAttributeToSelect('sku');
            $collection->addAttributeToFilter('entity_id', ['gt' => $lastId]);
            $collection->setPageSize($batch);
            $collection->setOrder('entity_id', 'ASC');

            $maxProcessedId = $lastId;
            foreach ($collection as $product) {
                $sku = (string)$product->getSku();
                try {
                    $result = $this->processSkuForce($sku);
                    $processed += $result['processed'];
                    $saved += $result['saved'];
                    $skipped += $result['skipped'];
                    $prodId = (int)$product->getId();
                    if ($prodId > $maxProcessedId) {
                        $maxProcessedId = $prodId;
                    }
                } catch (\Throwable $e) {
                    $this->logger->error('AI Related: generator error for ' . $sku . ': ' . $e->getMessage());
                    $this->logger->error('AI Related: exception details: ' . $e->__toString());
                }
            }
            // persist last processed id if we processed any
            $this->persistLastProcessedId($maxProcessedId, $lastId);
        } catch (\Throwable $e) {
            $this->logger->error('AI Related: generator fatal: ' . $e->getMessage());
        }

        return ['processed' => $processed, 'saved' => $saved, 'skipped' => $skipped];
    }

    /**
     * Decide the start entity id: explicit start, or cached value, default 0.
     */
    private function determineStartEntityId(?int $startEntityId = null): int
    {
        if ($startEntityId !== null) {
            return (int)$startEntityId;
        }

        // If we've recently persisted an id in this process, use it to avoid stale scope config reads
        if ($this->lastPersistedId !== null) {
            return (int)$this->lastPersistedId;
        }
        // Always read last processed id from DB config path
        try {
            $val = $this->scopeConfig->getValue('ecommerce66_ai/ai_related/last_entity_id');
            return $val !== null ? (int)$val : 0;
        } catch (\Throwable $e) {
            $this->logger->warning('AI Related: failed to read last_entity_id from DB config: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Persist last processed id if it advanced beyond previous.
     */
    private function persistLastProcessedId(int $maxProcessedId, int $previousLastId): void
    {
        if ($maxProcessedId <= $previousLastId) {
            return;
        }
        // Persist last processed id to DB config always
        try {
            $this->configWriter->save('ecommerce66_ai/ai_related/last_entity_id', (string)$maxProcessedId);
            // update in-memory value so subsequent generate() calls in the same CLI process see the change
            $this->lastPersistedId = (int)$maxProcessedId;
        } catch (\Throwable $e) {
            $this->logger->warning('AI Related: failed saving last_entity_id to DB config: ' . $e->getMessage());
        }
    }

    /**
     * Reset persisted progress (clear cache key)
     */
    public function resetProgress(): void
    {
        try {
            $this->configWriter->save('ecommerce66_ai/ai_related/last_entity_id', '0');
            // reflect reset in-memory as well
            $this->lastPersistedId = 0;
            $this->logger->info('AI Related: reset progress DB config ecommerce66_ai/ai_related/last_entity_id');
        } catch (\Throwable $e) {
            $this->logger->warning('AI Related: could not reset progress in DB: ' . $e->getMessage());
        }
    }

    /**
     * Return the last persisted entity id tracked in-memory for this process.
     * May be null if nothing has been persisted yet in this process.
     *
     * @return int|null
     */
    public function getLastPersistedId(): ?int
    {
        return $this->lastPersistedId;
    }
}
