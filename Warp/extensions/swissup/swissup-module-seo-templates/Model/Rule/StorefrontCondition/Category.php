<?php

namespace Swissup\SeoTemplates\Model\Rule\StorefrontCondition;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Rule\Model\Condition\Context;
use Swissup\SeoTemplates\Helper\Data as Helper;

class Category extends \Magento\Rule\Model\Condition\AbstractCondition
{
    private $attributeRepository;
    private $helper;
    private $searchCriteriaBuilder;

    public function __construct(
        ProductAttributeRepositoryInterface $attributeRepository,
        Helper $helper,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Context $context,
        array $data = []
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->helper = $helper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct($context, $data);
    }

    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        if ($this->getAttribute() === 'applied_filters') {
            $appliedFilets = $this->getAppliedFilters();
            $valueToValidate = [];
            $request = $this->helper->getRequest();

            foreach ($appliedFilets as $filter) {
                $values = explode(',', $request->getParam($filter->getAttributeCode()));
                foreach ($values as $value) {
                    $valueToValidate[] = $filter->getId() .':' . trim($value);
                }
            }

            return $this->validateAttribute($valueToValidate);
        }

        return parent::validate($model);
    }

    private function getAppliedFilters(): array
    {
        $ignore = ['id'];
        $params = $this->helper->getRequest()->getParams();
        $applied = array_diff_key($params, array_flip($ignore));

        if (empty($applied)) {
            return [];
        }

        $criteria = $this->searchCriteriaBuilder
            ->addFilter('attribute_code', implode(',', array_keys($applied)), 'in')
            ->create();

        return $this->attributeRepository->getList($criteria)->getItems();
    }
}
