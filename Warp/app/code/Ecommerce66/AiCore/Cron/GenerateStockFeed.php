<?php
declare(strict_types=1);

namespace Ecommerce66\AiCore\Cron;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Ecommerce66\AiCore\Helper\Feeds as FeedsHelper;

class GenerateStockFeed
{
    private const MEDIA_SUBDIR = 'e66';
    private const CSV_RELATIVE_PATH = self::MEDIA_SUBDIR . '/ai_stock_update.csv';
    private const JSON_RELATIVE_PATH = self::MEDIA_SUBDIR . '/ai_stock_update.json';
    private const CSV_HEADERS = ['sku', 'stock_status', 'price', 'special_price', 'minimal_price'];

    private CollectionFactory $collectionFactory;
    private Filesystem $filesystem;
    private Json $json;
    private StockRegistryInterface $stockRegistry;
    private LoggerInterface $logger;
    private FeedsHelper $feeds;
    private StoreManagerInterface $storeManager;

    /**
     * GenerateStockFeed constructor.
     *
     * @param CollectionFactory      $collectionFactory
     * @param Filesystem             $filesystem
     * @param Json                   $json
     * @param StockRegistryInterface $stockRegistry
     * @param LoggerInterface        $logger
     * @param FeedsHelper            $feeds
     * @param StoreManagerInterface  $storeManager
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Filesystem $filesystem,
        Json $json,
        StockRegistryInterface $stockRegistry,
        LoggerInterface $logger,
        FeedsHelper $feeds,
        StoreManagerInterface $storeManager
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->filesystem        = $filesystem;
        $this->json              = $json;
        $this->stockRegistry     = $stockRegistry;
        $this->logger            = $logger;
        $this->feeds             = $feeds;
        $this->storeManager      = $storeManager;
    }

    /**
     * Cron execute
     */
    public function execute(): void
    {
        try {
            $format = $this->feeds->getStockFormat(); // 'csv' | 'json'
            $media  = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            $media->create(self::MEDIA_SUBDIR);

            $collection = $this->prepareCollection();
            $rows       = $this->collectRows($collection);

            if ($format === 'json') {
                $this->writeJson($media, $rows);
                $this->logger->info('[E66 AiCore] Stock feed (JSON) generated: ' . $media->getAbsolutePath(self::JSON_RELATIVE_PATH));
                return;
            }

            $this->writeCsv($media, $rows);
            $this->logger->info('[E66 AiCore] Stock feed (CSV) generated: ' . $media->getAbsolutePath(self::CSV_RELATIVE_PATH));
        } catch (\Throwable $e) {
            $this->logger->error('[E66 AiCore] Stock feed error: ' . $e->getMessage(), ['exception' => $e]);
        }
    }

    /**
     * @return ProductCollection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function prepareCollection(): ProductCollection
    {
        $storeId    = (int) $this->storeManager->getStore()->getId();
        $collection = $this->collectionFactory->create();

        $collection->setStoreId($storeId);
        $collection->addAttributeToSelect(['sku', 'status', 'visibility', 'price', 'special_price']);
        $collection->addMinimalPrice();
        $collection->addFinalPrice();

        $collection->addAttributeToFilter('status', Status::STATUS_ENABLED);
        $collection->addAttributeToFilter('visibility', ['in' => [
            Visibility::VISIBILITY_IN_SEARCH,
            Visibility::VISIBILITY_BOTH
        ]]);

        $collection->setPageSize(1000)->setCurPage(1);
        $collection->load(); // load before last page calc

        return $collection;
    }

    /**
     * @param ProductCollection $collection
     *
     * @return array
     */
    private function collectRows(ProductCollection $collection): array
    {
        $rows  = [];
        $pages = max(1, (int) $collection->getLastPageNumber());

        for ($page = 1; $page <= $pages; $page++) {
            if ($page > 1) {
                $collection->clear();
                $collection->setCurPage($page);
                $collection->load();
            }
            foreach ($collection as $product) {
                $rows[] = $this->rowFromProduct($product);
            }
        }

        return $rows;
    }

    /**
     * @param Product $product
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function rowFromProduct(Product $product): array
    {
        $sku       = (string) $product->getSku();
        $stockItem = $this->stockRegistry->getStockItemBySku($sku);
        $inStock   = $stockItem ? (bool) $stockItem->getIsInStock() : false;

        return [
            'sku'            => $sku,
            'stock_status'   => $inStock ? 1 : 0,
            'price'          => $this->valueOrNull($product->getData('price')),
            'special_price'  => $this->valueOrNull($product->getData('special_price')),
            'minimal_price'  => $this->valueOrNull($product->getData('minimal_price')),
        ];
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    private function valueOrNull($value)
    {
        if ($value === '' || $value === null) {
            return null;
        }
        return is_scalar($value) ? $value : (string) $value;
    }

    /**
     * @param WriteInterface $media
     * @param array          $rows
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function writeJson(WriteInterface $media, array $rows): void
    {
        $payload = [
            'generated_at' => gmdate('c'),
            'total'        => count($rows),
            'items'        => $rows,
        ];
        $media->writeFile(self::JSON_RELATIVE_PATH, $this->json->serialize($payload));
    }

    /**
     * @param WriteInterface $media
     * @param array          $rows
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function writeCsv(WriteInterface $media, array $rows): void
    {
        $stream = $media->openFile(self::CSV_RELATIVE_PATH, 'w+');
        $stream->lock();

        $delimiter = $this->csvDelimiter();
        $enclosure = $this->csvEnclosure();

        $stream->writeCsv(self::CSV_HEADERS, $delimiter, $enclosure);

        foreach ($rows as $row) {
            $stream->writeCsv([
                $row['sku'],
                $row['stock_status'],
                $row['price'],
                $row['special_price'],
                $row['minimal_price'],
            ], $delimiter, $enclosure);
        }

        $stream->unlock();
        $stream->close();
    }

    /**
     * @return string
     */
    private function csvDelimiter(): string
    {
        return ';'; // or read from $this->feeds->getCsvDelimiter()
    }

    /**
     * @return string
     */
    private function csvEnclosure(): string
    {
        return '"'; // or read from $this->feeds->getCsvEnclosure()
    }
}
