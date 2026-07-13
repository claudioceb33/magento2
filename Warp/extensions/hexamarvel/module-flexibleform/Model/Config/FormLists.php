<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */
namespace Hexamarvel\FlexibleForm\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;
use Hexamarvel\FlexibleForm\Model\ResourceModel\Form\CollectionFactory as FormCollectionFactory;

class FormLists implements OptionSourceInterface
{

    
    /**
     * @var FormCollectionFactory
     */
    protected $formCollectionFactory;

    /**
     * @param FormCollectionFactory $formCollectionFactory
     */
    public function __construct(
        FormCollectionFactory $formCollectionFactory
    ) {
        $this->formCollectionFactory = $formCollectionFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $collection = $this->formCollectionFactory->create()->addFieldToSelect(['title','id'])->load();
        $options = [];

        $options[] = ['label' => __('-- Please Select a Form --'), 'value' => ''];

        if (!empty($collection) && count($collection) > 0) {
            foreach ($collection as $form) {
                 $options[] = ['label' => $form->getTitle(), 'value' => $form->getId()];
            }
        }

        return $options;
    }
}
