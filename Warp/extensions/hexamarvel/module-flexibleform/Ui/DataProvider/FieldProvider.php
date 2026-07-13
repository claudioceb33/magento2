<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Ui\DataProvider;

use Hexamarvel\FlexibleForm\Model\ResourceModel\Field\CollectionFactory;

class FieldProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $collection;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $categoryFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $categoryFactory,
        \Magento\Framework\App\Request\Http $request,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $categoryFactory->create();
        $this->request = $request;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        $items = $this->collection->getItems();
        $data = [];
        foreach ($items as $field) {
            $data[$field->getId()]['field_configuration'] = [
                'is_active' => $field->getData('is_active'),
                'title' => $field->getData('title'),
                'field_type' => $field->getData('field_type'),
                'option_values' => $field->getData('option_values'),
                'field_label' => $field->getData('field_label'),
                'fieldset' => $field->getData('fieldset'),
                'field_note' => $field->getData('field_note'),
                'field_class' => $field->getData('field_class'),
                'is_required' => $field->getData('is_required'),
                'layout' => $field->getData('layout'),
                'position' => $field->getData('position'),
                'tooltip' => $field->getData('tooltip'),
                'placeholder' =>  $field->getData('placeholder'),
                'id' => $this->request->getParam('field_id'),
            ];
        }

        if (!empty($data)) {
            return $data;
        }
    }
}
