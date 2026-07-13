<?php

namespace Swissup\SeoImages\Model\Filter;

use Swissup\SeoCore\Model\Filter\AbstractFilter;

class Product extends AbstractFilter
{
    const OUTPUT_MAX_LENGTH = 180;

    /**
     * @var \Swissup\SeoCore\Model\Slug
     */
    private $slug;

    /**
     * @param \Swissup\SeoCore\Model\Slug             $slug
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     */
    public function __construct(
        \Swissup\SeoCore\Model\Slug $slug,
        \Magento\Framework\Filter\FilterManager $filterManager
    ) {
        $this->slug = $slug;
        parent::__construct($filterManager);
    }

    /**
     * {@inheritdoc}
     */
    protected function _getAttributeValue($attributeCode)
    {
        // Workaround to work at admin product grid.
        $scope = $this->getScope();
        if (!$scope->getResource()) {
            return $scope->getData($attributeCode);
        }

        return parent::_getAttributeValue($attributeCode);
    }

    /**
     * {@inheritdoc}
     */
    protected function _postprocessResult($result, $paramsArray)
    {
        $output = parent::_postprocessResult($result, $paramsArray);
        $filterManager = $this->filterManager;

        return $filterManager->truncate(
            $this->slug->slugify($output),
            [
                'length' => self::OUTPUT_MAX_LENGTH,
                'etc' => ''
            ]
        );
    }

    /**
     * Product name directive.
     *
     * @param  array $construction
     * @return string
     */
    public function nameDirective($construction)
    {
        $construction[2] .= ' code="name"';

        return $this->attributeDirective($construction);
    }

    /**
     * Product SKU directive.
     *
     * @param  array $construction
     * @return string
     */
    public function skuDirective($construction)
    {
        $construction[2] .= ' code="sku"';

        return $this->attributeDirective($construction);
    }
}
