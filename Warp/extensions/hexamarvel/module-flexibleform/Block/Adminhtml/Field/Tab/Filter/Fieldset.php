<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Block\Adminhtml\Field\Tab\Filter;

class Fieldset extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    /**
     * @var array
     */
    protected static $_statuses;

    /**
     * @param \Hexamarvel\FlexibleForm\Model\FieldSetFactory
     */
    protected $fieldSetFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Hexamarvel\FlexibleForm\Model\FieldSetFactory $fieldSetFactory
     * @param array $data = []
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Hexamarvel\FlexibleForm\Model\FieldSetFactory $fieldSetFactory,
        array $data = []
    ) {
        $this->fieldSetFactory = $fieldSetFactory;
        parent::__construct($context, $resourceHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $fieldSets = $this->fieldSetFactory->create()->getCollection()
        ->addFieldToFilter(
            'form_id',
            $this->getRequest()->getParam('id', false)
        );

        self::$_statuses = [
            null => null,
            '0'  => 'Default'
        ];
        foreach ($fieldSets as $key => $fieldset) {
            self::$_statuses[$fieldset->getId()] = $fieldset->getTitle();
        }
        parent::_construct();
    }

    /**
     * @return array
     */
    protected function _getOptions()
    {
        $options = [];
        foreach (self::$_statuses as $status => $label) {
            $options[] = ['value' => $status, 'label' => __($label)];
        }

        return $options;
    }

    /**
     * @return array|null
     */
    public function getCondition()
    {
        return $this->getValue() === null ? null : ['eq' => $this->getValue()];
    }
}
