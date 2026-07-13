<?php
declare(strict_types=1);

namespace Ecommerce66\AiCore\Cron;

use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\File\Csv as CsvWriter;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
use Ecommerce66\AiCore\Helper\Feeds as FeedsHelper;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;

class GenerateCatalogFeed
{
    private ProductCollectionFactory $productCollectionFactory;
    private CategoryCollectionFactory $categoryCollectionFactory;
    private Filesystem $filesystem;
    private CsvWriter $csvWriter;
    private Json $json;
    private LoggerInterface $logger;
    private StockRegistryInterface $stockRegistry;
    private FeedsHelper $feeds;
    private IoFile $ioFile;
    private StoreManagerInterface $storeManager;

    /**
     * GenerateCatalogFeed constructor.
     *
     * @param ProductCollectionFactory  $productCollectionFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param Filesystem                $filesystem
     * @param CsvWriter                 $csvWriter
     * @param Json                      $json
     * @param LoggerInterface           $logger
     * @param StockRegistryInterface    $stockRegistry
     * @param FeedsHelper               $feeds
     * @param IoFile                    $ioFile
     * @param StoreManagerInterface     $storeManager
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        Filesystem $filesystem,
        CsvWriter $csvWriter,
        Json $json,
        LoggerInterface $logger,
        StockRegistryInterface $stockRegistry,
        FeedsHelper $feeds,
        IoFile $ioFile,
        StoreManagerInterface $storeManager
    ) {
        $this->productCollectionFactory  = $productCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->filesystem   = $filesystem;
        $this->csvWriter    = $csvWriter;
        $this->json         = $json;
        $this->logger       = $logger;
        $this->stockRegistry= $stockRegistry;
        $this->feeds        = $feeds;
        $this->ioFile       = $ioFile;
        $this->storeManager = $storeManager;
    }

    /**
     * Cron execute
     */
    public function execute(): void
    {
        try {
            $format      = $this->feeds->getCatalogFormat();
            $extraFields = $this->feeds->getCatalogExtraAttributes();
            $fields      = $this->buildFieldList($extraFields);

            // Recorremos todos los websites
            foreach ($this->storeManager->getWebsites() as $website) {
                $websiteCode = (string)$website->getCode();
                $storeId     = (int)$website->getDefaultStore()->getId();

                $target = $this->prepareTargetPath($format, $websiteCode);

                $collection = $this->prepareCollection($extraFields, $storeId);
                $collection->load();
                $pages = max(1, (int)$collection->getLastPageNumber());
                $rows  = $this->buildRows($collection, $fields, $extraFields, $pages, $storeId);

                if ($format === 'json') {
                    $this->writeJson($target['fileName'], $rows);
                    $this->logger->info(sprintf(
                        '[E66 AiCore] Catalog feed (JSON) generated for website "%s": %s',
                        $websiteCode,
                        $target['absolute']
                    ));
                    continue;
                }

                $this->writeCsv($target['relative'], $fields, $rows);
                $this->logger->info(sprintf(
                    '[E66 AiCore] Catalog feed (CSV) generated for website "%s": %s',
                    $websiteCode,
                    $target['absolute']
                ));
            }
        } catch (\Throwable $e) {
            $this->logger->error('[E66 AiCore] Catalog feed error: ' . $e->getMessage(), ['exception' => $e]);
        }
    }

    /**
     * @param array $extraFields
     *
     * @return array
     */
    private function buildFieldList(array $extraFields): array
    {
        // Campos base + 'additional_attributes' al final
        $base = FeedsHelper::DEFAULT_FIELDS;
        $unique = array_values(array_unique($base));
        // 'additional_attributes' es una única columna/campo
        $unique[] = 'additional_attributes';

        // Los extra NO van como columnas planas: se incluirán en 'additional_attributes'
        return $unique;
    }

    /**
     * @param string $format
     *
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function prepareTargetPath(string $format, string $websiteCode): array
    {
        $media = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $media->create('e66');

        $ext      = ($format === 'json') ? 'json' : 'csv';
        $relative = sprintf('e66/ai_catalog_feed_%s.%s', $websiteCode, $ext);
        $absolute = $media->getAbsolutePath($relative);

        return ['fileName' => $relative, 'absolute' => $absolute, 'relative' => $relative];
    }


    /**
     * @param array $extraFields
     *
     * @return Collection
     */
    private function prepareCollection(array $extraFields, int $storeId): Collection
    {
        $brandSrc = $this->feeds->getBrandSourceAttribute();

        $attrSelect = [
            'sku','name','price','special_price','minimal_price','small_image','url_key',
            // manufacturer: siempre exportamos el campo "manufacturer" pero el valor viene del atributo configurado
            $brandSrc
        ];

        // Añadir extras (los exportaremos dentro de 'additional_attributes')
        foreach ($extraFields as $code) {
            if (!in_array($code, $attrSelect, true)) {
                $attrSelect[] = $code;
            }
        }

        $collection = $this->productCollectionFactory->create();
        $collection->setStoreId($storeId); // <-- IMPORTANTE: contexto por website
        $collection->addAttributeToSelect($attrSelect);
        $collection->addMinimalPrice();
        $collection->addAttributeToFilter('status', Status::STATUS_ENABLED);
        $collection->addAttributeToFilter('visibility', ['in' => [
            Visibility::VISIBILITY_IN_SEARCH,
            Visibility::VISIBILITY_BOTH
        ]]);
        $collection->setPageSize(500)->setCurPage(1);

        return $collection;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param string[] $fields
     * @param string[] $extraFields
     * @param int $pages
     * @return array<int,array<string,mixed>>
     */
    private function buildRows(
        Collection $collection,
        array $fields,
        array $extraFields,
        int $pages,
        int $storeId
    ): array {
        $rows = [];

        for ($page = 1; $page <= $pages; $page++) {
            if ($page > 1) {
                // clear + set page + load (sin recalcular pages otra vez)
                $collection->clear();
                $collection->setCurPage($page);
                $collection->load();
            }

            // Mapa de nombres de categorías SOLO para esta página
            $categoryMap = $this->buildCategoryNameMapForPage($collection);

            foreach ($collection as $product) {
                $rows[] = $this->buildRowForProduct($product, $fields, $extraFields, $categoryMap, $storeId);
            }
        }

        return $rows;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param array                          $categoryNameMap
     *
     * @return array
     */
    private function getProductCategoryNames(\Magento\Catalog\Model\Product $product, array $categoryNameMap): array
    {
        $ids = $product->getCategoryIds();
        if (empty($ids)) {
            return [];
        }
        $names = [];
        foreach ($ids as $id) {
            if (isset($categoryNameMap[$id])) {
                $names[] = $categoryNameMap[$id];
            }
        }
        return $names;
    }

    /**
     * @param Collection $collection
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function buildCategoryNameMapForPage(Collection $collection): array
    {
        $allIds = [];
        foreach ($collection as $p) {
            $ids = $p->getCategoryIds();
            if (!empty($ids)) {
                foreach ($ids as $id) {
                    $allIds[$id] = true;
                }
            }
        }

        if (empty($allIds)) {
            return [];
        }

        $catCollection = $this->categoryCollectionFactory->create();
        $catCollection->addAttributeToSelect('name');
        $catCollection->addFieldToFilter('entity_id', ['in' => array_keys($allIds)]);

        $map = [];
        foreach ($catCollection as $cat) {
            $map[(int)$cat->getId()] = (string)$cat->getName();
        }
        return $map;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param array                          $fields
     * @param array                          $extraFields
     * @param array                          $categoryNameMap
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function buildRowForProduct(
        \Magento\Catalog\Model\Product $product,
        array $fields,
        array $extraFields,
        array $categoryNameMap,
        int $storeId
    ): array {
        // Asegurar contexto de store para URL y atributos scopeados
        $product->setStoreId($storeId);

        // stock_status
        $sku        = (string)$product->getSku();
        $stockItem  = $this->stockRegistry->getStockItemBySku($sku);
        $inStock    = $stockItem ? (bool)$stockItem->getIsInStock() : false;
        $stockLabel = $inStock ? 1 : 0;

        // categories
        $catNames = $this->getProductCategoryNames($product, $categoryNameMap);
        $categoriesPipe = empty($catNames) ? '' : implode('|', $catNames);

        // manufacturer (mapeado)
        $manufacturerLabel = $this->getManufacturerValue($product);
        //$brandCode = (string)$this->feeds->getBrandSourceAttribute();

        // additional_attributes
        $additional = $this->buildAdditionalAttributes($product, $extraFields);

        // URLs completas
        $smallImageUrl = $this->getSmallImageFullUrl($product, $storeId);
        $productUrl    = $this->getProductFullUrl($product, $storeId);

        $specialValues = [
            'stock_status'          => $stockLabel,
            'categories'            => $categoriesPipe,
            'brand'                 => $manufacturerLabel,
            'additional_attributes' => $additional,
            // overrides
            'small_image'           => $smallImageUrl,
            'url_key'               => $productUrl,
        ];

        $row = [];
        foreach ($fields as $f) {
            $row[$f] = array_key_exists($f, $specialValues)
                ? $specialValues[$f]
                : $this->normalizeValue($this->getAttributeLabelValue($product, $f));
        }

        return $row;
    }

    /**
     * Devuelve la URL completa (https) de small_image para el website actual.
     */
    private function getSmallImageFullUrl(\Magento\Catalog\Model\Product $product, int $storeId): ?string
    {
        $val = (string)$product->getData('small_image');
        if ($val === '' || $val === 'no_selection' || $val === null) {
            return null;
        }

        // Normalizar path (puede venir con o sin prefijo /catalog/product)
        $path = ltrim($val, '/');
        if (strpos($path, 'catalog/product/') !== 0) {
            $path = 'catalog/product/' . $path;
        }

        $store   = $this->storeManager->getStore($storeId);
        $baseUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA, true); // true => secure
        return rtrim($baseUrl, '/') . '/' . $path;
    }

    /**
     * Devuelve la URL absoluta del producto (con sufijo si aplica) para el website actual.
     */
    private function getProductFullUrl(\Magento\Catalog\Model\Product $product, int $storeId): string
    {
        // Aseguramos contexto de store
        $product->setStoreId($storeId);
        // Usa UrlModel nativo (respeta rewrites y sufijo)
        return (string)$product->getUrlModel()->getUrl($product);
    }

    /**
     * Normalize EAV value: empty string/null -> null, arrays/objects -> string cast.
     *
     * @param mixed $value
     * @return mixed
     */
    private function normalizeValue($value)
    {
        if ($value === '' || $value === null) {
            return null;
        }
        return is_scalar($value) ? $value : (string)$value;
    }

    /**
     * Return manufacturer/brand label for single-select attributes.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function getManufacturerValue(\Magento\Catalog\Model\Product $product): ?string
    {
        $brandCode = (string) $this->feeds->getBrandSourceAttribute();

        // 1) Try custom brand attribute (admin store label)
        $attr = $product->getResource()->getAttribute($brandCode);
        if ($attr && $attr->getId()) {
            $raw = $product->getData($brandCode);
            if ($raw === '' || $raw === null) {
                // fallback to "manufacturer" string if custom brand empty
                $fallback = (string) $product->getData('manufacturer');
                return $fallback !== '' ? $fallback : null;
            }

            if ($attr->usesSource()) {
                // Force admin store (store 0) to avoid translations
                if (method_exists($attr, 'setStoreId')) {
                    $attr->setStoreId(0);
                }
                $text = $attr->getSource()->getOptionText($raw);
                if (is_string($text) && $text !== '') {
                    return $text;
                }
                // Some sources may return false/null if ID not found; fallback to raw as string
                return is_scalar($raw) ? (string) $raw : (string) $raw;
            }

            // Attribute exists but has no source: treat as plain text
            return is_scalar($raw) ? (string) $raw : (string) $raw;
        }

        // 2) If custom brand attribute does not exist, fallback to default "manufacturer" text value
        $man = $product->getData('manufacturer');
        if ($man === '' || $man === null) {
            return null;
        }
        return is_scalar($man) ? (string) $man : (string) $man;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param array                          $extraFields
     *
     * @return array
     */
    private function buildAdditionalAttributes(\Magento\Catalog\Model\Product $product, array $extraFields): array
    {
        $additional = [];
        foreach ($extraFields as $code) {
            // Evitar duplicar base fields
            if (in_array($code, FeedsHelper::DEFAULT_FIELDS, true)) {
                continue;
            }
            $val = $this->getAttributeLabelValue($product, $code);
            $additional[$code] = $this->normalizeValue($val);
        }

        // Append variant SKUs for configurable products
        $variantSkus = $this->getVariantSkus($product);
        if (!empty($variantSkus)) {
            $additional['variant_skus'] = $variantSkus;
        }

        return $additional;
    }

    /**
     * Return attribute display value (label) for select/multiselect, otherwise raw value.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $attributeCode
     * @return string|array<int,string>|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function getAttributeLabelValue(\Magento\Catalog\Model\Product $product, string $attributeCode)
    {
        $attr = $product->getResource()->getAttribute($attributeCode);
        if (!$attr || !$attr->getId()) {
            return $product->getData($attributeCode);
        }

        $raw = $product->getData($attributeCode);
        if ($raw === '' || $raw === null) {
            return null;
        }

        if ($attr->usesSource()) {
            // Use admin store for stable labels (same approach as brand)
            if (method_exists($attr, 'setStoreId')) {
                $attr->setStoreId(0);
            }

            $text = $attr->getSource()->getOptionText($raw);

            // multiselect can return array
            if (is_array($text)) {
                //$text = array_values(array_filter(array_map('strval', $text)));
                //return $text ?: null;
                return implode('|', array_values(array_filter(array_map('strval', $text))));
            }

            if (is_string($text) && $text !== '') {
                return $text;
            }

            // fallback if source returns false/null
            return is_scalar($raw) ? (string)$raw : (string)$raw;
        }

        return $raw;
    }

    /**
     * Return variant SKUs when product is configurable.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string[]
     */
    private function getVariantSkus(\Magento\Catalog\Model\Product $product): array
    {
        if ($product->getTypeId() !== ConfigurableType::TYPE_CODE) {
            return [];
        }

        // Prefer fast path: getUsedProducts
        $typeInstance = $product->getTypeInstance();
        if (method_exists($typeInstance, 'getUsedProducts')) {
            $skus = [];
            foreach ($typeInstance->getUsedProducts($product) as $child) {
                $sku = (string) $child->getSku();
                if ($sku !== '') {
                    $skus[] = $sku;
                }
            }
            return $skus;
        }

        // Fallback: getChildrenIds + load minimal collection (sku only)
        if (method_exists($typeInstance, 'getChildrenIds')) {
            $idsMap = (array) $typeInstance->getChildrenIds((int) $product->getId());
            $childIds = [];
            foreach ($idsMap as $group) {
                foreach ((array) $group as $id) {
                    $childIds[] = (int) $id;
                }
            }
            if ($childIds) {
                $col = $this->productCollectionFactory->create();
                $col->addAttributeToSelect(['sku'])
                    ->addIdFilter($childIds);
                return array_values(array_filter(array_map('strval', $col->getColumnValues('sku'))));
            }
        }

        return [];
    }

    /**
     * @param string $mediaFileName
     * @param array  $rows
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function writeJson(string $mediaFileName, array $rows): void
    {
        $media = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $payload = [
            'generated_at' => gmdate('c'),
            'total'        => count($rows),
            'items'        => $rows,
        ];
        $media->writeFile($mediaFileName, $this->json->serialize($payload));
    }

    /**
     * @param string $absoluteFilePath
     * @param array  $fields
     * @param array  $rows
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function writeCsv(string $relativeFilePath, array $fields, array $rows): void
    {
        $media = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $media->create('e66');

        $stream = $media->openFile($relativeFilePath, 'w+'); // crea/trunca
        $stream->lock();

        $delimiter = ';'; // o $this->feeds->getCsvDelimiter();
        $enclosure = '"'; // o $this->feeds->getCsvEnclosure();

        // Header
        $stream->writeCsv($fields, $delimiter, $enclosure);

        // Rows
        foreach ($rows as $row) {
            $line = [];
            foreach ($fields as $f) {
                if ($f === 'additional_attributes') {
                    $line[] = $this->json->serialize($row[$f] ?? []);
                    continue;
                }
                $line[] = $row[$f] ?? null;
            }
            $stream->writeCsv($line, $delimiter, $enclosure);
        }

        $stream->unlock();
        $stream->close();

        $this->logger->info('[E66 AiCore] Catalog feed (CSV) generated: ' . $media->getAbsolutePath($relativeFilePath));
    }

}
