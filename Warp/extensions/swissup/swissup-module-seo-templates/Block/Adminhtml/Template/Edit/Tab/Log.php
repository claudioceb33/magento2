<?php

namespace Swissup\SeoTemplates\Block\Adminhtml\Template\Edit\Tab;

class Log extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry = null;

    /**
     * @var  \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Swissup\SeoTemplates\Model\ResourceModel\Log\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Swissup\SeoTemplates\Model\ResourceModel\Log\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('seotemlates_log_grid');
        $this->setDefaultSort('log_time');
        $this->setDefaultDir('asc');
        $this->setUseAjax(true);
    }

    /**
     * Apply various selection filters to prepare the sales order grid collection.
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->collectionFactory->create();
        if ($templateId = $this->registry->registry('seotemplates_template_id')) {
            $collection->addFieldToFilter('template_id', $templateId);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'log_id', [
                'header'=> __('ID'),
                'type'  => 'number',
                'index' => 'id',
            ]
        )->addColumn(
            'log_entity_id',
            [
                'header'=> __('Entity Id'),
                'index' => 'entity_id',
            ]
        )->addColumn(
            'log_store_id',
            [
                'header' => __('Store View'),
                'index'  => 'store_id',
                'type'      => 'options',
                'options' => $this->getStoresOptions()
            ]
        )->addColumn(
            'log_value',
            [
                'header'=> __('Value'),
                'index' => 'generated_value',
            ]
        )->addColumn(
            'log_time',
            [
                'header'=> __('Generated at'),
                'index' => 'generation_time',
                'type'  => 'datetime',
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Retrieve the Url for a specified log row.
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return '#';
    }

    /**
     * {@inheritdoc}
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/log', ['_current' => true]);
    }

    /**
     * Get options for 'Store View' column
     *
     * @return array
     */
    public function getStoresOptions()
    {
        $options = [];
        foreach ($this->_storeManager->getStores(true) as $store) {
            if ($store->getId() == 0) {
                $options[$store->getId()] = __('All Store Views');
            } else {
                $options[$store->getId()] = $store->getName();
            }
        }

        return $options;
    }
}
