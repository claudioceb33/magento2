<?php

namespace Swissup\ProLabels\Model\Rule\Condition;

use Magento\Rule\Model\Condition\Context;
use Swissup\ProLabels\Model\Stock as StockModel;

class Stock extends \Magento\Rule\Model\Condition\AbstractCondition
{
    /**
     * @var StockModel
     */
    private $stock;

    /**
     * @param StockModel $stock
     * @param Context    $context
     * @param array      $data
     */
    public function __construct(
        StockModel $stock,
        Context $context,
        array $data = []
    ) {
        $this->stock = $stock;
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
        if ($attrCode === 'status') {
            $value = (int)$this->stock->isInStock($product);
        } elseif ($attrCode === 'qty') {
            $value = $this->stock->getQty($product);
        }

        return $this->validateAttribute($value);
    }

    /**
     * @inheritdoc
     */
    public function loadAttributeOptions() {
        $this->setData('attribute_option', [
            'status' => __('Stock Status'),
            'qty' => __('Stock Quantity')
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
        if ($this->getAttribute() === 'status') {
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
            case 'status':
                return 'select';

            case 'qty':
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
            if ($this->getAttribute() === 'status') {
                $valueOption = [
                    0 => __('Out of stock'),
                    1 => __('In stock')
                ];
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
