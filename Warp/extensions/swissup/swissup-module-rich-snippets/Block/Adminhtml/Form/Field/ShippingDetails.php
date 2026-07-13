<?php

namespace Swissup\RichSnippets\Block\Adminhtml\Form\Field;

class ShippingDetails extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $rendererCountry = $this->getLayout()->createBlock(Country::class, '', [
            'data' => [
                'is_render_to_js_template' => true,
                'class' => 'country_select'
            ]
        ]);
        $rendererMethod = $this->getLayout()->createBlock(ShippingMethod::class, '', [
            'data' => [
                'is_render_to_js_template' => true,
                'class' => 'method_select'
            ]
        ]);
        $this->addColumn('country', ['label' => __('Country'), 'renderer' => $rendererCountry]);
        $this->addColumn('method', ['label' => __('Shipping method'), 'renderer' => $rendererMethod]);
        $this->addColumn('handling', ['label' => __('Handling time')]);
        $this->addColumn('transit', ['label' => __('Transit time')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add shipping configuration');
    }

    /**
     * Prepare existing row data object
     *
     * @param \Magento\Framework\DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        foreach (['country', 'method'] as $name) {
            $column = $this->getColumns()[$name];
            if ($renderer = $column['renderer'] ?? null) {
                $optionExtraAttr['option_' . $renderer->calcOptionHash($row->getData($name))] =
                    'selected="selected"';
            }
        }

        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }

    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = parent::render($element);
        $html = str_replace(
            '<td class="label"',
            '<td class="label" style="display: none"',
            $html
        );
        $html = str_replace(
            '<td class="value"',
            '<td class="value" colspan="2" style="padding: 2.2rem 2rem 2.2rem 0"',
            $html
        );

        return $html;
    }
}
