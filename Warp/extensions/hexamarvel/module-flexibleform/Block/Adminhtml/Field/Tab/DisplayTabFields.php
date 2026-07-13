<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Block\Adminhtml\Field\Tab;

use Magento\Backend\Block\Widget\Grid\Extended;

class DisplayTabFields extends Extended
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Hexamarvel\FlexibleForm\Model\FieldFactory
     */
    protected $_fieldSetFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Hexamarvel\FlexibleForm\Model\FieldFactory $fieldSetFactory
     * @param array $data = []
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Hexamarvel\FlexibleForm\Model\FieldFactory $fieldFactory,
        array $data = []
    ) {
        $this->_productFactory = $productFactory;
        $this->_fieldFactory = $fieldFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('display_tab_grid');
        $this->setDefaultSort('id');
        $this->setUseAjax(true);
    }

    /**
     * @return string buttonHtml
     */
    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();
        if ($this->getRequest()->getParam('id', false)) {
            $addUrl = $this->getUrl('hexaform/field/new', ['form_id' => $this->getRequest()->getParam('id', false)]);
            $addButton = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
            ->setData([
                'label'     => __('Add Field'),
                'onclick'   => "setLocation('" . $addUrl . "')",
                'class'   => 'primary'
            ])->toHtml();
            return $addButton . $html;
        }

        return $html;
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $collection = $this->_fieldFactory->create()->getCollection();
        $id = (int)$this->getRequest()->getParam('id', false);
        if ($id) {
            $collection->addFieldToFilter(
                'form_id',
                $id
            );
        } else {
            $collection->addFieldToFilter(
                'form_id',
                '0'
            );
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn('title', ['header' => __('Title'), 'index' => 'title']);
        $this->addColumn('fieldset', [
            'header' => __('Fieldset'),
            'index' => 'fieldset',
            'filter' => \Hexamarvel\FlexibleForm\Block\Adminhtml\Field\Tab\Filter\Fieldset::class,
            'renderer'  => \Hexamarvel\FlexibleForm\Block\Adminhtml\Field\Tab\Renderer\Fieldset::class,
        ]);
        $this->addColumn('field_class', ['header' => __('Class'), 'index' => 'field_class']);
        $this->addColumn('position', ['header' => __('Position'), 'index' => 'position']);
        $this->addColumn('is_active', [
            'header' => __('Enabled'),
            'filter' => \Hexamarvel\FlexibleForm\Block\Adminhtml\FieldSet\Tab\Filter\Status::class,
            'renderer'  => \Hexamarvel\FlexibleForm\Block\Adminhtml\FieldSet\Tab\Renderer\Status::class,
            'index' => 'is_active'
        ]);
        $this->addColumn(
            'action',
            [
                'header' => __('Actions'),
                'renderer'  => \Hexamarvel\FlexibleForm\Block\Adminhtml\FieldSet\Tab\Renderer\Actions::class,
                'type' => 'action',
                'getter' => 'getId',
                'sortable' => false,
                'filter' => false,
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'class' => '',
                        'url' => [
                            'base' => '*/field/edit',
                            'params' => ['form_id' => $this->getRequest()->getParam('id', false)]
                        ],
                        'field' => 'field_id'
                    ],
                    [
                        'caption' => __('Delete'),
                        'url' => [
                            'base' => '*/field/delete',
                            'params' => ['form_id' => $this->getRequest()->getParam('id', false)]
                        ],
                        'field' => 'id'
                    ]
                ],
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('hexaform/field/tabgrid', ['_current' => true]);
    }
}
