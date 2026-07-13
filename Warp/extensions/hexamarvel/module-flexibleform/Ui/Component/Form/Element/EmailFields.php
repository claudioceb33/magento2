<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Ui\Component\Form\Element;

class EmailFields implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Hexamarvel\FlexibleForm\Model\FieldFactory
     */
    protected $fieldFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @param \Hexamarvel\FlexibleForm\Model\FieldFactory $fieldFactory
     */
    public function __construct(
        \Hexamarvel\FlexibleForm\Model\FieldFactory $fieldFactory,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->fieldFactory = $fieldFactory;
        $this->request = $request;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $fieldSetCollection = $this->fieldFactory->create()->getCollection()
        ->addFieldToFilter(
            'is_active',
            '1'
        )->addFieldToFilter(
            'form_id',
            $this->request->getParam('id')
        )->addFieldToFilter(
            'field_type',
            'email'
        )->setOrder(
            'position',
            'asc'
        );
        $options = [];
        $options[] = ['label' => 'Select Field', 'value' => '0'];
        foreach ($fieldSetCollection as $key => $fieldSet) {
            $options[] = ['label' => $fieldSet->getTitle(), 'value' => $fieldSet->getId()];
        }

        return $options;
    }
}
