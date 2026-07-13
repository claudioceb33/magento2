<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Block\Email;

class FormFields extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Hexamarvel\FlexibleForm\Model\ResourceModel\Field\CollectionFactory
     */
    protected $fieldFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Hexamarvel\FlexibleForm\Model\ResourceModel\Field\CollectionFactory $fieldFactory
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Hexamarvel\FlexibleForm\Model\ResourceModel\Field\CollectionFactory $fieldFactory
    ) {
        $this->fieldFactory = $fieldFactory;
        parent::__construct($context);
    }

    /**
     * @param $fieldId int
     * @return array
     */
    public function getFieldTitle($fieldId)
    {
        $formField = $this->fieldFactory->create()
            ->addFieldToFilter('id', $fieldId)
            ->addFieldToFilter('field_type', ['nin' => ['terms', 'hidden', 'file' , 'image']])
            ->load()->getFirstItem()->getTitle();

        return $formField;
    }
}
