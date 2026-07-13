<?php
namespace Swissup\FieldManager\Model\ResourceModel\Customer\Form\Attribute;

use Magento\Customer\Model\ResourceModel\Form\Attribute\Collection as CustomerFormAttributeCollection;

class Collection extends CustomerFormAttributeCollection
{
    /**
     * Fix to make collection work with Magento\Ui\Component\MassAction\Filter
     *
     * @see \Swissup\FieldManager\Controller\Adminhtml\Index\MassStatus
     *
     * @return string
     */
    public function getIdFieldName()
    {
        return 'main_table.attribute_id';
    }

    /**
     * @inheritdoc
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->_map['fields']['attribute_id'] = 'main_table.attribute_id';

        return $this;
    }

    protected function _beforeLoad()
    {
        // add this filter in _beforeLoad only, as it depends on
        // joined eav_attribute table
        $this->addFieldToFilter('frontend_input', ['neq' => 'hidden'])
            ->setOrder('ca.sort_order', self::SORT_ORDER_ASC);

        return parent::_beforeLoad();
    }
}
