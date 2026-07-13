<?php
declare(strict_types=1);

namespace Ecommerce66\AiSearch\Plugin;

use Magento\Search\Model\Autocomplete;
use Magento\Search\Model\Autocomplete\ItemFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Ecommerce66\AiSearch\Helper\Data as AiSearchHelper;

class AutocompletePlugin
{
    private AiSearchHelper $config;
    private ItemFactory $itemFactory;
    private RequestInterface $request;
    private UrlInterface $urlBuilder;
    private StoreManagerInterface $storeManager;
    private PriceHelper $priceHelper;

    public function __construct(
        AiSearchHelper $config,
        ItemFactory $itemFactory,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        PriceHelper $priceHelper
    ) {
        $this->config        = $config;
        $this->itemFactory   = $itemFactory;
        $this->request       = $request;
        $this->urlBuilder    = $urlBuilder;
        $this->storeManager  = $storeManager;
        $this->priceHelper   = $priceHelper;
    }

    public function afterGetItems(Autocomplete $subject, array $result): array
    {
        if (!$this->config->isEnabled()) {
            return $result;
        }
        $types = $this->config->getSearchType();
        if (!$types) {
            return $result;
        }
        $query = trim((string)$this->request->getParam('q', ''));
        if ($query === '') {
            return $result;
        }

        $haveNative = !empty($result);
        $mode = $this->resolveMode($types, $haveNative);
        if ($mode === 'magento' || $mode === 'fallback_native') {
            return $result;
        }

        $limit   = $this->config->getSearchItems();
        $aiItems = $this->fetchAiItems($query, $limit);

        if (empty($aiItems)) {
            return $mode === 'ai' ? [] : $result;
        }

        return $mode === 'hybrid' ? array_merge($result, $aiItems) : $aiItems;
    }

    private function resolveMode(array $types, bool $haveNative): string
    {
        $only = count($types) === 1;
        $set  = array_flip($types);
        if ($only && isset($set['magento']))  { return 'magento'; }
        if ($only && isset($set['fallback'])) { return $haveNative ? 'fallback_native' : 'fallback_nonative'; }
        if (isset($set['hybrid']))            { return 'hybrid'; }
        if ($only && isset($set['ai']))       { return 'ai'; }
        return 'hybrid';
    }

    private function fetchAiItems(string $query, int $limit): array
    {
        $resp   = $this->config->searchAi($query, $limit);
        $status = (int)($resp['status'] ?? 0);
        $body   = is_array($resp['body'] ?? null) ? $resp['body'] : null;
        if ($status < 200 || $status >= 300 || !$body) {
            return [];
        }

        $rows = $body['results'] ?? [];
        if (!is_array($rows) || !$rows) {
            return [];
        }

        $items = [];
        foreach (array_slice($rows, 0, $limit) as $row) {
            $mapped = $this->mapAiRowToItem($row, $query);
            if ($mapped) {
                $items[] = $mapped;
            }
        }
        return $items;
    }

    private function mapAiRowToItem(array $row, string $fallbackQuery)
    {
        $title = (string)($row['title'] ?? '');
        $sku   = (string)($row['sku'] ?? '');
        $name  = $title !== '' ? $title : $sku;
        if ($name === '') {
            return null;
        }

        // Si tu API trae url como slug, por ahora redirigimos a la búsqueda con ese texto:
        $url = $row['url'];

        $imageUrl = (string)($row['image_url'] ?? '');
        $image    = $this->absoluteImageUrlOrPlaceholder($imageUrl);

        $brand    = (string)($row['brand'] ?? '');
        $priceHtml = $this->buildPriceHtml(
            $row['price'] ?? null,
            $row['special_price'] ?? null,
            $row['minimal_price'] ?? null
        );

        return $this->itemFactory->create([
            // clave para que ES elija nuestro template:
            'type'       => 'e66_ai',
            'row_class'  => 'e66-ai-item',
            'title'      => $name,
            'url'        => $url,
            'image'      => $image,
            'brand'      => $brand,
            'price_html' => $priceHtml,
            // opcional: num_results si querés mostrar un contador
            'num_results'=> ''
        ]);
    }

    private function absoluteImageUrlOrPlaceholder(string $imageUrl): string
    {
        if ($imageUrl !== '' && str_starts_with($imageUrl, 'http')) {
            return $imageUrl;
        }
        if ($imageUrl !== '') {
            $base = rtrim($this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB), '/');
            // si la API te da rutas absolutas del media (/x/y.jpg), las respetamos como absolutas desde WEB
            return $base . $imageUrl;
        }
        // placeholder simple (podés parametrizar por config si querés)
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
            . 'catalog/product/placeholder/small_image.jpg';
    }

    /**
     * @param $price
     * @param $special
     * @param $minimal
     *
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function buildPriceHtml($price, $special, $minimal): string
    {
        $p  = $this->numOrNull($price);
        $sp = $this->numOrNull($special);
        $mp = $this->numOrNull($minimal);

        // special < price  => old (tachado) + special
        if ($p !== null && $sp !== null && $sp > 0 && $sp < $p) {
            return sprintf(
                '<span class="e66-ai-price-old">%s</span><span class="e66-ai-price">%s</span>',
                $this->priceHelper->currency($p, true, false),
                $this->priceHelper->currency($sp, true, false)
            );
        }

        // minimal > 0 => "from minimal"
        if ($mp !== null && $mp > 0 && $mp != $p) {
            return sprintf(
                '<span class="e66-ai-price-from">%s %s</span>',
                __('Desde'),
                $this->priceHelper->currency($mp, true, false)
            );
        }

        // default => price si > 0; si no hay, vacío
        if ($p !== null && $p > 0) {
            return sprintf(
                '<span class="e66-ai-price">%s</span>',
                $this->priceHelper->currency($p, true, false)
            );
        }

        return '';
    }

    private function numOrNull($v): ?float
    {
        if ($v === null || $v === '' || $v === 0 || $v === '0' || $v === 0.0) {
            return null;
        }
        return (float)$v;
    }
}
