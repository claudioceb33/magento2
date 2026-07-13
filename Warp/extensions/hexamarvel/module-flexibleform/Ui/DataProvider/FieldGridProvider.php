<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Ui\DataProvider;

use Magento\Customer\Ui\Component\Listing\AttributeRepository;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;
use Hexamarvel\FlexibleForm\Model\ResourceModel\Form\CollectionFactory;

class FieldGridProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{

    protected $collection;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Reporting $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param AttributeRepository $attributeRepository
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Reporting $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );

        $this->collection = $collectionFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        $this->addFilterOverride(
            $this->collection,
            $filter->getField(),
            [$filter->getConditionType() => $filter->getValue()],
            $filter
        );
    }

    /**
     * @param $collection
     * @param $field
     * @param $condition = null
     */
    private function addFilterOverride($collection, $field, $condition, $filter)
    {
        if (isset($condition['fulltext']) && (string)$condition['fulltext'] !== '') {
            $needle = $condition['fulltext'];
            $collection->addFieldToFilter(
                'title',
                [
                    'like' => '%'.$needle.'%'
                ]
            );

            $ids = [];
            foreach ($collection as $key => $value) {
                $ids[] = $value->getId();
            }

            if (empty($ids)) {
                $ids [] = -1;
            }

            $filterCollection = $this->filterBuilder->setField('id')
                ->setConditionType('in')
                ->setValue($ids)
                ->create();
            $this->searchCriteriaBuilder->addFilter($filterCollection);
        } else {
            $this->searchCriteriaBuilder->addFilter($filter);
        }
    }
}
