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

namespace Lof\Formbuilder\Block\Adminhtml\Form\Edit\Tab;

use Exception;
use Lof\Formbuilder\Model\Message;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\LinkFactory;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Registry;

class Messages extends Extended
{
    /**
     * Core registry
     *
     * @var Registry|null
     */
    protected $coreRegistry = null;

    /**
     * @var LinkFactory
     */
    protected LinkFactory $linkFactory;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $setsFactory;

    /**
     * @var ProductFactory
     */
    protected ProductFactory $productFactory;

    /**
     * @var Type
     */
    protected Type $type;

    /**
     * @var Status
     */
    protected Status $status;

    /**
     * @var Visibility
     */
    protected Visibility $visibility;
    protected Message $message;

    /**
     * Messages constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param LinkFactory $linkFactory
     * @param CollectionFactory $setsFactory
     * @param ProductFactory $productFactory
     * @param Type $type
     * @param Status $status
     * @param Visibility $visibility
     * @param Registry $coreRegistry
     * @param Message $message
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        LinkFactory $linkFactory,
        CollectionFactory $setsFactory,
        ProductFactory $productFactory,
        Type $type,
        Status $status,
        Visibility $visibility,
        Registry $coreRegistry,
        Message $message,
        array $data = []
    ) {
        $this->linkFactory = $linkFactory;
        $this->setsFactory = $setsFactory;
        $this->productFactory = $productFactory;
        $this->type = $type;
        $this->status = $status;
        $this->visibility = $visibility;
        $this->coreRegistry = $coreRegistry;
        $this->message = $message;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Set grid params
     *
     * @return void
     * @throws FileSystemException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('messages_grid');
        $this->setDefaultSort('message_id');
        $this->setUseAjax(true);
        if ($this->getForm() && $this->getForm()->getFormId()) {
            $this->setDefaultFilter(['in_forms' => 1]);
        }
        if ($this->isReadonly()) {
            $this->setFilterVisibility(false);
        }
    }

    /**
     * @return mixed|null
     */
    public function getForm()
    {
        return $this->coreRegistry->registry('current_form');
    }

    /**
     * Prepare collection
     *
     * @inheritdoc
     */
    protected function _prepareCollection()
    {
        $collection = $this->message->getCollection()->addFieldToFilter('form_id', $this->getForm()->getId());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Checks when this block is readonly
     *
     * @return bool
     */
    public function isReadonly(): bool
    {
        return $this->getForm() && $this->getForm()->getCrosssellReadonly();
    }

    /**
     * Add columns to grid
     *
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
            'creation_time',
            [
                'header' => __('Created At'),
                'index' => 'creation_time',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );

        $this->addColumn(
            'gaction',
            [
                'header' => __('Action'),
                'type' => 'action',
                'renderer'  => 'Lof\Formbuilder\Block\Adminhtml\Form\Renderer\MessageAction',
            ]
            );

        return parent::_prepareColumns();
    }

    /**
     * Retrieve grid URL
     *
     * @return string
     */
    public function getGridUrl(): string
    {
        return $this->_getData(
            'grid_url'
        ) ? $this->_getData(
            'grid_url'
        ) : $this->getUrl(
            'formbuilder/form/messagesGrid',
            [
                'form_id' => $this->getForm()->getId()
            ]
        );
    }
}
