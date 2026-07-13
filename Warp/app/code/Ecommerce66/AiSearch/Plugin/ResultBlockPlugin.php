<?php
declare(strict_types=1);

namespace Ecommerce66\AiSearch\Plugin;

use Magento\CatalogSearch\Block\Result as ResultBlock;
use Ecommerce66\AiSearch\Helper\Data as AiSearchConfig;

class ResultBlockPlugin
{
    private AiSearchConfig $config;

    public function __construct(AiSearchConfig $config)
    {
        $this->config = $config;
    }

    /**
     * In AI-only mode, suppress native Magento/ElasticSuite results.
     */
    public function aroundGetProductListHtml(ResultBlock $subject, \Closure $proceed): string
    {
        $types = $this->config->getSearchType();
        if (in_array('ai', $types, true) && count($types) === 1) {
            // Solo AI -> ocultar resultados nativos
            return '';
        }
        return $proceed();
    }
}
