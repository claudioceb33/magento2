<?php

namespace Swissup\HreflangImportExport\Model\Export\AbstractEntity;

use Magento\Framework\Data\Collection as AttributeCollection;
use Magento\Framework\Data\Collection\AbstractDb as DbCollection;
use Magento\Framework\Exception\LocalizedException;
use Magento\ImportExport\Model\Export;

abstract class CollectionFactory
{
    protected $collectionFactory;

    public function __construct(
        $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function create(
        AttributeCollection $attributeCollection,
        array $filters
    ): DbCollection {
        $collection = $this->collectionFactory->create();
        $this->joinHreflang($collection);

        foreach ($this->retrieveFilterData($filters) as $columnName => $value) {
            $attributeDefinition = $attributeCollection->getItemById($columnName);
            if (!$attributeDefinition) {
                throw new LocalizedException(__(
                    'Given column name "%columnName" is not present in collection.',
                    ['columnName' => $columnName]
                ));
            }

            $this->addFilter(
                $collection,
                $columnName,
                $attributeDefinition->getData('backend_type'),
                $value
            );
        }

        return $collection;
    }

    protected function addFilter(
        DbCollection $collection,
        string $field,
        string $fieldType,
        $value
    ): void {
        if (!$fieldType) {
            throw new LocalizedException(__(
                'There is no backend type specified for column "%columnName".',
                ['columnName' => $field]
            ));
        }

        $condition = [];
        switch ($fieldType) {
            case 'varchar':
                if ($field === 'hreflang_links') {
                    $ids = explode(',', $value);
                    $condition['in'] = array_map('intval', $ids);
                } else {
                    $condition['like'] = "%{$value}%";
                }
                break;

            case 'int':
                if (is_array($value)) {
                    $from = $value[0] ?? null;
                    $to = $value[1] ?? null;

                    if (is_numeric($from) && !empty($from)) {
                        $condition['from'] = $from;
                    }

                    if (is_numeric($to) && !empty($to)) {
                        $condition['to'] = $to;
                    }
                } else {
                    $condition['eq'] = $value;
                }

                break;
        }

        if (!empty($condition)) {
            $collection->addFieldToFilter($field, $condition);
        }
    }

    abstract protected function joinHreflang(DbCollection $collection): void;

    private function retrieveFilterData(array $filters)
    {
        return array_filter(
            $filters[Export::FILTER_ELEMENT_GROUP] ?? [],
            function ($value) {
                return $value !== '';
            }
        );
    }
}
