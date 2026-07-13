<?php

namespace Swissup\ProLabels\Model\Rule\Condition;

use Magento\Rule\Model\Condition\Context;
use Magento\CatalogRule\Model\Rule\Condition\ProductFactory as ConditionFactory;

class Combine extends \Magento\CatalogRule\Model\Rule\Condition\Combine
{
    private General $conditionGeneral;
    private Stock $conditionStock;

    public function __construct(
        General $conditionGeneral,
        Stock $conditionStock,
        Context $context,
        ConditionFactory $conditionFactory,
        array $data = []
    ) {
        $this->conditionGeneral = $conditionGeneral;
        $this->conditionStock = $conditionStock;
        parent::__construct($context, $conditionFactory, $data);
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $conditions = parent::getNewChildSelectOptions();

        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'label' => __('Product Stock'),
                    'value' => $this->getConditionItems($this->conditionStock)
                ]
            ]
        );

        $conditions = array_merge(
            array_slice($conditions, 0, 2),
            [
                [
                    'label' => __('General'),
                    'value' => $this->getConditionItems($this->conditionGeneral)
                ]
            ],
            array_slice($conditions, 2)
        );

        return $conditions;
    }

    private function getConditionItems($conditionsObject): array
    {
        if (!$conditionsObject->hasData('attribute_option')) {
            $conditionsObject->loadAttributeOptions();
        }

        $conditions = [];
        foreach ($conditionsObject->getAttributeOption() as $code => $label) {
            $conditions[] = [
                'value' => get_class($conditionsObject) . '|' . $code,
                'label' => $label
            ];
        }

        return $conditions;
    }
}
