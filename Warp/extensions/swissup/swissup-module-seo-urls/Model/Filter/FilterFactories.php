<?php

namespace Swissup\SeoUrls\Model\Filter;

class FilterFactories extends \Magento\Framework\DataObject
{
    /**
     * Construct
     *
     * @param CategoryFactory  $categoryFilter
     * @param StockFactory     $stockFilter
     * @param RatingFactory    $ratingFilter
     * @param AttributeFactory $attributeFilter
     * @param array            $data
     */
    public function __construct(
        CategoryFactory $categoryFilter,
        StockFactory $stockFilter,
        RatingFactory $ratingFilter,
        AttributeFactory $attributeFilter,
        array $data = []
    ) {
        $data += [
            'category_filter' => $categoryFilter,
            'stock_filter' => $stockFilter,
            'rating_filter' => $ratingFilter,
            'attribute_filter' => $attributeFilter
        ];
        parent::__construct($data);
    }

    /**
     * Create SEO filter wrapper
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        switch (substr($method, 0, 6)) {
            case 'create':
                $filterName = $this->_underscore(substr($method, 6));
                // $index = isset($args[0]) ? $args[0] : null;
                return $this->createFilter($filterName);
        }

        return parent::__call($method, $args);
    }

    /**
     * Create SEO filter by name
     *
     * @param  string $filterName
     * @return mixed
     */
    public function createFilter($filterName)
    {
        $factory = $this->getData($filterName);
        if (isset($factory)) {
            return $factory->create();
        }

        return null;
    }
}
