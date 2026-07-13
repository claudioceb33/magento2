<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Block\Adminhtml\FormData;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Hexamarvel\FlexibleForm\Model\ResourceModel\FormData\CollectionFactory;
use Hexamarvel\FlexibleForm\Model\FieldFactory;

class Grid extends Extended
{
    /**
     * CollectionFactory
     */
    protected $formDataFactory;

    /**
     * FieldFactory
     */
    protected $fieldFactory;

    /**
     * @param Context $context,
     * @param Data $backendHelper,
     * @param CollectionFactory $formDataFactory,
     * @param FieldFactory $fieldFactory,
     * @param array $data = [],
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $formDataFactory,
        FieldFactory $fieldFactory,
        array $data = []
    ) {
        $this->formDataFactory = $formDataFactory;
        $this->fieldFactory = $fieldFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('index');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() != 'id' && $column->getId() != 'created_on') {
            if ($column->getFilter()->getValue()) {
                $formData = $this->formDataFactory->create();
                $search = $column->getFilter()->getValue();
                $formIds = [];
                $index = str_replace('formdata', '', $column->getId());
                foreach ($formData as $key => $data) {
                    $dataValue = json_decode($data->getValue(), true);
                    if (isset($dataValue[$index]) && strtolower($dataValue[$index]) == strtolower($search)) {
                        $formIds[] = $data->getId();
                    }
                }

                $this->getCollection()->addFieldToFilter('id', ['in' => $formIds]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * @return obj
     */
    protected function _prepareCollection()
    {
        $formData = $this->formDataFactory->create()->addFieldToSelect('*');
        $formData->addFieldToFilter('form_id', $this->getRequest()->getParam('form_id', false));
        $this->setCollection($formData);
        return parent::_prepareCollection();
    }

    /**
     * @return obj
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'id',
                'align' => 'center',
                'index' => 'id',
            ]
        );
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );

        $collection = $this->fieldFactory->create()->getCollection();
        $collection->addFieldToFilter('form_id', $this->getRequest()->getParam('form_id', false));
        foreach ($collection as $key => $value) {
            if ($value->getFieldType() != 'terms') {
                $columArray = [
                    'header' => $value->getTitle(),
                    'type' => 'text',
                    'index' => $value->getId(),
                    'header_css_class' => 'col-id',
                    'column_css_class' => 'col-id',
                    'sortable' => false,
                    'renderer'  => \Hexamarvel\FlexibleForm\Block\Adminhtml\FormData\Renderer\Value::class,
                ];
                if ($value->getFieldType() == 'image' || $value->getFieldType() == 'file') {
                    $columArray['filter']= false;
                }

                $this->addColumn(
                    'formdata'.$value->getId(),
                    $columArray
                );
            }
        }

        $this->addColumn(
            'created_on',
            [
                'header' => __('Submitted Date'),
                'type' => 'date',
                'index' => 'created_on',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('hexaform/formdata/index', ['form_id' => $this->getRequest()->getParam('form_id')]);
    }

    /**
     * @inheritDoc
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('selected');

        $this->getMassactionBlock()->addItem(
            'export',
            [
                'label' => __('Export'),
                'url' => $this->getUrl('hexaform/*/export'),
                'confirm' => __('Do you want to export selected items?')
            ]
        );
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('hexaform/*/delete'),
                'confirm' => __('Are you sure, Do you want to export selected items?')
            ]
        );

        return $this;
    }
}
