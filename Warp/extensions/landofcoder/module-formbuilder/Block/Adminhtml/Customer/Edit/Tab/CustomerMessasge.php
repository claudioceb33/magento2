<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_Formbuilder
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\Formbuilder\Block\Adminhtml\Customer\Edit\Tab;

use Exception;
use Lof\Formbuilder\Block\Adminhtml\Customer\Renderer\MessageAction;
use Lof\Formbuilder\Model\MessageFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Registry;

class CustomerMessasge extends Extended
{
    /**
     * Core registry
     *
     * @var Registry|null
     */
    protected $coreRegistry = null;

    /**
     * @var Status
     */
    protected $status;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * CustomerMessasge constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param Status $status
     * @param Registry $coreRegistry
     * @param MessageFactory $messageFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        Status $status,
        Registry $coreRegistry,
        MessageFactory $messageFactory,
        array $data = []
    ) {
        $this->status = $status;
        $this->coreRegistry = $coreRegistry;
        $this->messageFactory = $messageFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Set grid params
     *
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('formbuilder_message_grid');
        $this->setDefaultSort('creation_time');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    /**
     * @return mixed|null
     */
    public function getCustomer()
    {
        return $this->coreRegistry->registry('current_customer');
    }

    /**
     * Add filter
     *
     * @param mixed $column
     * @return $this
     */
    protected function _addColumnFilterToCollection(mixed $column)
    {
        parent::_addColumnFilterToCollection($column);
        return $this;
    }

    /**
     * Prepare collection
     *
     * @inheritdoc
     */
    protected function _prepareCollection()
    {
        $collection = $this->messageFactory->create()->getCollection();
        $customer = $this->getCustomer();
        $collection->addFieldToFilter('main_table.customer_id', $customer->getId());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @inheritdoc
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'message_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'message_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'form_id',
            [
                'header' => __('Form ID'),
                'index' => 'form_id',
                'header_css_class' => 'col-increment_id',
                'column_css_class' => 'col-increment_id'
            ]
        );

        $this->addColumn(
            'safe_message',
            [
                'header' => __('Message'),
                'index' => 'safe_message',
                'header_css_class' => 'col-increment_id',
                'column_css_class' => 'col-increment_id'
            ]
        );

        $this->addColumn(
            'creation_time',
            [
                'header' => __('Created At'),
                'index' => 'creation_time',
                'header_css_class' => 'col-increment_id',
                'column_css_class' => 'col-increment_id'
            ]
        );

        $this->addColumn(
            'gaction',
            [
                'header' => __('Action'),
                'type' => 'action',
                'renderer' => MessageAction::class,
            ]
        );
        return parent::_prepareColumns();
    }
}
