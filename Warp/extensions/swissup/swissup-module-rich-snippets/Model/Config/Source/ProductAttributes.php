<?php

namespace Swissup\RichSnippets\Model\Config\Source;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class ProductAttributes implements \Magento\Framework\Option\ArrayInterface
{
    private ProductAttributeRepositoryInterface $attributeRepository;

    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param SearchCriteriaBuilder               $searchCriteriaBuilder
     */
    public function __construct(
        ProductAttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $criteria = $this->getSearchCriteria();
        $attributes = $this->attributeRepository->getList($criteria)->getItems();
        $options = [
            [
                'value' => '',
                'label' => __('None')
            ]
        ];
        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $options[] = [
                    'value' => $attributeCode,
                    'label' => $attribute->getStoreLabel() . " [{$attributeCode}]"
                ];
        }

        return $options;
    }

    /**
     * Search Criteria for attributes list
     *
     * @return \Magento\Framework\Api\SearchCriteria
     */
    protected function getSearchCriteria()
    {
        return $this->searchCriteriaBuilder->create();
    }
}
