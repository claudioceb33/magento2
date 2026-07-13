<?php

namespace Swissup\SeoTemplates\Model\Rule\Condition;

class Category extends \Magento\Rule\Model\Condition\Product\AbstractProduct
{
    /**
     * Customize default operator input by type mapper for some types
     *
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            parent::getDefaultOperatorInputByType();
            $this->_defaultOperatorInputByType['category'] = ['()', '!()', '==', '!='];
            $this->_defaultOperatorInputByType['filter'] = ['()', '!()', '==', '!='];
        }
        return $this->_defaultOperatorInputByType;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $attrCode = $this->getAttribute();
        switch ($attrCode) {
            case 'category_ids':
                return $this->validateAttribute($model->getId());
                break;

            case 'applied_filters':
                return true;
                break;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getInputType()
    {
        if ($this->getAttributeObject()->getAttributeCode() == 'applied_filters') {
            return 'filter';
        }

        return parent::getInputType();
    }

    /**
     * {@inheritdoc}
     */
    protected function _addSpecialAttributes(array &$attributes)
    {
        parent::_addSpecialAttributes($attributes);
        $attributes['applied_filters'] = __('Filter');
    }

    /**
     * {@inheritdoc}
     */
    public function getValueElementChooserUrl()
    {
        if ($this->getAttribute() === 'applied_filters') {
            $url = 'seotemplates/template/chooser/attribute/' . $this->getAttribute();
            if ($this->getJsFormObject()) {
                $url .= '/form/' . $this->getJsFormObject();
            }

            return $this->_backendData->getUrl($url);
        }

        return parent::getValueElementChooserUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function getValueAfterElementHtml()
    {
        if ($this->getAttribute() === 'applied_filters') {
            $image = $this->_assetRepo->getUrl('images/rule_chooser_trigger.gif');
            $html = '<a href="javascript:void(0)" class="rule-chooser-trigger"><img src="' .
                $image .
                '" alt="" class="v-middle rule-chooser-trigger" title="' .
                __(
                    'Open Chooser'
                ) . '" /></a>';

            return $html;
        }

        return parent::getValueAfterElementHtml();
    }

    /**
     * {@inheritdoc}
     */
    public function getExplicitApply()
    {
        if ($this->getAttribute() === 'applied_filters') {
            return true;
        }

        return parent::getExplicitApply();
    }
}
