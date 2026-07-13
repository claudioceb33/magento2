<?php

namespace Swissup\RichSnippets\Block\Adminhtml\Form\Field;

class OpeningHours extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var Customergroup
     */
    protected $_groupRenderer;

    /**
     * Retrieve days of week column renderer
     *
     * @return DaysOfWeek
     */
    protected function _getGroupRenderer()
    {
        if (!$this->_groupRenderer) {
            $this->_groupRenderer = $this->getLayout()->createBlock(
                DaysOfWeek::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_groupRenderer->setClass('days_of_week_select');
        }
        return $this->_groupRenderer;
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'day_of_week',
            ['label' => __('Day of Week'), 'renderer' => $this->_getGroupRenderer()]
        );
        $this->addColumn('opens', ['label' => __('Opens')]);
        $this->addColumn('closes', ['label' => __('Closes')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add day');
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
        $optionExtraAttr['option_' . $this->_getGroupRenderer()->calcOptionHash($row->getData('day_of_week'))] =
            'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }
}
