<?php

/**
 * SEO Templates Combine Condition data model
 */
namespace Swissup\SeoTemplates\Model\Rule\Condition;

use Swissup\SeoTemplates\Model\Template;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $conditionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $conditionFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->productFactory = $conditionFactory;
        $this->registry = $registry;
        parent::__construct($context, $data);
        $this->setType(\Swissup\SeoTemplates\Model\Rule\Condition\Combine::class);
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
                        'value' => \Swissup\SeoTemplates\Model\Rule\Condition\Combine::class,
                        'label' => __('Conditions Combination'),
                    ]
                ]
            );

        if ($this->isConditionAllowed(Template::ENTITY_TYPE_PRODUCT)) {
            $productAttributes = $this->productFactory->create()->loadAttributeOptions()->getAttributeOption();
            $attributes = [];
            foreach ($productAttributes as $code => $label) {
                $attributes[] = [
                    'value' => 'Magento\CatalogRule\Model\Rule\Condition\Product|' . $code,
                    'label' => $label,
                ];
            }

            $conditions = array_merge_recursive(
                $conditions,
                [
                    [
                        'label' => __('Product Attribute'),
                        'value' => $attributes
                    ]
                ]
            );
        }

        if ($this->isConditionAllowed(Template::ENTITY_TYPE_CATEGORY)) {
            $conditions = array_merge_recursive(
                $conditions,
                [
                    [
                        'label' => __('Category Attribute'),
                        'value' => [
                            [
                                'label' => __('Category ID'),
                                'value' => 'Swissup\SeoTemplates\Model\Rule\Condition\Category|category_ids'
                            ]
                        ]
                    ],
                    [
                        'label' => __('Layered Navigation'),
                        'value' => [
                            [
                                'label' => __('Applied filter'),
                                'value' => 'Swissup\SeoTemplates\Model\Rule\Condition\Category|applied_filters'
                            ]
                        ]
                    ]
                ]
            );
        }

        return $conditions;
    }

    /**
     * @param array $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            /** @var Product|Combine $condition */
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }

    /**
     * Is condition allowed (depends from template type)
     * @param  int $entityType
     * @return boolean
     */
    public function isConditionAllowed(
        $entityType = Template::ENTITY_TYPE_PRODUCT
    ) {
        $templateType = $this->registry->registry('seotemplates_template_type');
        return $templateType == $entityType;
    }
}
