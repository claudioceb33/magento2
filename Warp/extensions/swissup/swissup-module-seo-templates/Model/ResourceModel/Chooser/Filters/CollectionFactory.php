<?php

namespace Swissup\SeoTemplates\Model\ResourceModel\Chooser\Filters;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection as OptionsCollection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as OptionsCollectionFactory;
use Magento\Store\Model\Store;
use Magento\Catalog\Model\Product;

class CollectionFactory
{
    private $optionsCollectionFactory;

    public function __construct(
        OptionsCollectionFactory $optionsCollectionFactory
    ) {
        $this->optionsCollectionFactory = $optionsCollectionFactory;
    }

    public function create()
    {
        $collection = $this->optionsCollectionFactory->create();
        $this
            ->joinAttributeData($collection)
            ->joinValueData($collection)
            ->filterProductAttribute($collection)
            ->filterFilterableAttribute($collection);

        return $collection;
    }

    private function joinValueData(
        OptionsCollection $collection
    ) {
        $collection
            ->join(
                ['v' => 'eav_attribute_option_value'],
                implode(' AND ', [
                    'v.option_id = main_table.option_id',
                    'v.store_id = ' . Store::DEFAULT_STORE_ID
                ]),
                ['filter_value' => 'v.value']
            );

        $collection->addFilterToMap('filter_value', 'v.value');

        return $this;
    }

    private function joinAttributeData(
        OptionsCollection $collection
    ) {
        $filterKeyExpr = $collection->getConnection()->getConcatSql(
            ['a.attribute_id', 'main_table.option_id'],
            ':'
        );
        $collection
            ->join(
                ['a' => 'eav_attribute'],
                'a.attribute_id = main_table.attribute_id',
                [
                    'filter_key' => $filterKeyExpr,
                    'filter_name' => 'a.frontend_label'
                ]
            );

        $collection->addFilterToMap('filter_key', $filterKeyExpr);
        $collection->addFilterToMap('filter_name', 'a.frontend_label');

        return $this;
    }

    private function filterProductAttribute(
        OptionsCollection $collection
    ) {
        $collection->join(
                ['t' => 'eav_entity_type'],
                implode(' AND ', [
                    't.entity_type_id = a.entity_type_id',
                    't.entity_type_code = "'. Product::ENTITY .'"'
                ]),
                []
            );

        return $this;
    }

    private function filterFilterableAttribute(
        OptionsCollection $collection
    ) {
        $collection->join(
                ['c' => 'catalog_eav_attribute'],
                implode(' AND ', [
                    'c.attribute_id = a.attribute_id',
                    'c.is_filterable > 0'
                ]),
                []
            );

        return $this;
    }
}
