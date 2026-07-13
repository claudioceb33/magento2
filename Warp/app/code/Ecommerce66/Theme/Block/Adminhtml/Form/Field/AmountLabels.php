<?php
namespace Ecommerce66\Theme\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class AmountLabels extends AbstractFieldArray
{
    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('label', ['label' => __('Message'), 'class' => 'required-entry']);
        $this->addColumn('amount', ['label' => __('Minimum amount'), 'class' => 'number']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
