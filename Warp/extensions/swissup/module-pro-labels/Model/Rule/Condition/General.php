<?php

namespace Swissup\ProLabels\Model\Rule\Condition;

use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Rule\Model\Condition\Context;
use Swissup\ProLabels\Model\Price;

class General extends \Magento\Rule\Model\Condition\AbstractCondition
{
    private ConfigInterface $productTypeConfig;
    private Price $price;

    public function __construct(
        ConfigInterface $productTypeConfig,
        Price $price,
        Context $context,
        array $data = []
    ) {
        $this->productTypeConfig = $productTypeConfig;
        $this->price = $price;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    public function validate(
        \Magento\Framework\Model\AbstractModel $product
    ) {
        $attrCode = $this->getAttribute();

        $value = null;
        if ($attrCode === 'product_type') {
            $value = $product->getTypeId();
        } elseif ($attrCode === 'final_price') {
            if (!$product->hasData('final_price')) {
                $product->load($product->getId());
            }

            $value = $this->price->getFinalPrice($product);
        }

        return $this->validateAttribute($value);
    }

    /**
     * @inheritdoc
     */
    public function loadAttributeOptions()
    {
        $this->setData('attribute_option', [
            'product_type' => __('Product type'),
            'final_price' => __('Product final price')
        ]);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function loadValueOptions()
    {
        parent::loadValueOptions();
        $this->unsValueOption();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    /**
     * @inheritdoc
     */
    public function getValueElementType()
    {
        if ($this->getAttribute() === 'product_type') {
            return 'select';
        }

        return 'text';
    }

    /**
     * @inheritdoc
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'product_type':
                return 'select';

            case 'final_price':
                return 'numeric';

            default:
                return parent::getInputType();
        }
    }

    /**
     * @inheritdoc
     */
    public function getValueSelectOptions()
    {
        $this->_prepareValueOptions();
        return parent::getValueSelectOptions();
    }

    /**
     * Prepare value options base on attribute code
     *
     * @return $this
     */
    protected function _prepareValueOptions()
    {
        if (!$this->hasValueOption()) {
            $valueOption = [];
            if ($this->getAttribute() === 'product_type') {
                $valueOption = array_map(function ($type) {
                    return $type['label'] ?? '';
                }, $this->productTypeConfig->getAll());
            }

            $this->setValueOption($valueOption);
        }

        return $this;
    }

    /**
     * Collect validated attributes
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        return $this;
    }
}
