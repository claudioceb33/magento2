<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Ui\Component\Form\Element;

class FieldSet implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Hexamarvel\FlexibleForm\Model\FieldSetFactory
     */
    protected $fieldSetFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @param \Hexamarvel\FlexibleForm\Model\FieldSetFactory $fieldSetFactory
     */
    public function __construct(
        \Hexamarvel\FlexibleForm\Model\FieldSetFactory $fieldSetFactory,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->fieldSetFactory = $fieldSetFactory;
        $this->request = $request;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $fieldSetCollection = $this->fieldSetFactory->create()->getCollection()
        ->addFieldToFilter(
            'form_id',
            $this->request->getParam('form_id')
        )->setOrder(
            'position',
            'asc'
        );
        $options = [];
        $options[] = ['label' => 'Default', 'value' => '0'];
        foreach ($fieldSetCollection as $key => $fieldSet) {
            $options[] = ['label' => $fieldSet->getTitle(), 'value' => $fieldSet->getId()];
        }

        return $options;
    }
}
