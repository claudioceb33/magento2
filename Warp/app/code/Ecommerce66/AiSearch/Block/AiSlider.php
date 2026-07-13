<?php
declare(strict_types=1);

namespace Ecommerce66\AiSearch\Block;

use Ecommerce66\AiSearch\Helper\Data as AiSearchHelper;
use Ecommerce66\Widgets\Block\Widget\GridSliderProducts as BaseWidget;

use Magento\Catalog\Block\Product\Context as ProductContext;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Rule\Model\Condition\Sql\Builder as SqlBuilder;
use Magento\CatalogWidget\Model\Rule;
use Magento\Widget\Helper\Conditions;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\Url\EncoderInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;

use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Render AI results as a product slider using the existing widget template.
 *
 * Best practices:
 * - No heavy logic in PHTML.
 * - DI for helpers; no ObjectManager.
 * - Cache varies by query string.
 */
class AiSlider extends BaseWidget
{
    private AiSearchHelper $helper;
    private RequestInterface $request;
    private LoggerInterface $logger;

    public function __construct(
        ProductContext $context,
        CollectionFactory $productCollectionFactory,
        Visibility $catalogProductVisibility,
        HttpContext $httpContext,
        SqlBuilder $sqlBuilder,
        Rule $rule,
        Conditions $conditionsHelper,

        // <-- Tus dependencias extras pueden ir aquí (antes de $data) o después de $data
        AiSearchHelper $helper,
        LoggerInterface $logger,

        array $data = [],
        Json $json = null,
        LayoutFactory $layoutFactory = null,
        EncoderInterface $urlEncoder = null,
        CategoryRepositoryInterface $categoryRepository = null
    ) {
        $this->helper  = $helper;
        $this->logger  = $logger;
        $this->request = $context->getRequest(); // evita inyectar RequestInterface aparte

        parent::__construct(
            $context,
            $productCollectionFactory,
            $catalogProductVisibility,
            $httpContext,
            $sqlBuilder,
            $rule,
            $conditionsHelper,
            $data,
            $json,
            $layoutFactory,
            $urlEncoder,
            $categoryRepository
        );
    }

    /**
     * @return BaseWidget|\Magento\Framework\View\Element\AbstractBlock
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _beforeToHtml()
    {
        // Guard clauses para no renderizar vacío
        if (!$this->helper->isEnabled()) {
            $this->setData('multi_product_chooser', '');
            return parent::_beforeToHtml();
        }

        $q = trim((string)$this->request->getParam('q', ''));
        if ($q === '') {
            $this->setData('multi_product_chooser', '');
            return parent::_beforeToHtml();
        }

        try {
            $limit = max(1, (int)$this->helper->getSearchItems());
            $resp   = $this->helper->searchAi($q, $limit);
            $status = (int)($resp['status'] ?? 0);
            $body   = is_array($resp['body'] ?? null) ? $resp['body'] : null;

            if ($status < 200 || $status >= 300 || !$body) {
                $this->setData('multi_product_chooser', '');
                return parent::_beforeToHtml();
            }

            $rows = is_array($body['results'] ?? null) ? $body['results'] : [];
            if (!$rows) {
                $this->setData('multi_product_chooser', '');
                return parent::_beforeToHtml();
            }

            $skus = [];
            foreach ($rows as $row) {
                $sku = (string)($row['sku'] ?? '');
                if ($sku !== '') {
                    $skus[] = $sku;
                    if (count($skus) >= $limit) {
                        break;
                    }
                }
            }

            // Inyecta los SKUs al widget y configura el slider
            $this->setData('multi_product_chooser', implode(',', $skus));
            $this->setData('template_selector', 'Ecommerce66_AiSearch::widget/productscarousel.phtml');
            $this->setData('show_pager', 0);
            $this->setData('products_per_page', 5);
            $this->setData('show_addtocart', 1);
            $this->setData('show_whislist', $this->getData('show_whislist') ?? 0);
            $this->setData('show_compare', $this->getData('show_compare') ?? 0);

            $this->setData('grid_group_id', 'ia-result-'.time());

            // Responsive desde System Config
            $this->setData('slider_elements_desk', (string)$this->helper->getSliderElementsDesk());
            $this->setData('slider_elements_tablet', (string)$this->helper->getSliderElementsTablet());
            $this->setData('slider_elements_mobile', (string)$this->helper->getSliderElementsMobile());

            $this->setData('products_count', $limit);
            if (!$this->hasData('cache_lifetime')) {
                $this->setData('cache_lifetime', 600);
            }
        } catch (\Throwable $e) {
            $this->logger->error('[E66 AiSearch] Slider build failed: ' . $e->getMessage(), ['exception' => $e]);
            $this->setData('multi_product_chooser', '');
        }

        return parent::_beforeToHtml();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCacheKeyInfo()
    {
        $info = parent::getCacheKeyInfo();
        $info[] = 'E66_AI_SLIDER';
        $info[] = (string)$this->request->getParam('q', '');
        $info[] = (string)$this->helper->getSliderElementsDesk();
        $info[] = (string)$this->helper->getSliderElementsTablet();
        $info[] = (string)$this->helper->getSliderElementsMobile();
        return $info;
    }

    /**
     * @return bool
     */
    public function canShowResultBlock()
    {
        if (!$this->helper->canShowResultBlock()) {
            return false;
        }

        if ($this->helper->isFallbackOnly()) {
            if ($this->hasNativeResults()) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function hasNativeResults(): bool
    {
        $resultBlock = $this->getLayout()->getBlock('search.result');
        if ($resultBlock && method_exists($resultBlock, 'getResultCount')) {
            return ((int) $resultBlock->getResultCount()) > 0;
        }
        // Conservador si no hay forma de determinarlo
        return true;
    }

}
