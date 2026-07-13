<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Block\Adminhtml\Field;

class DisplayTabFields extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'fieldset/fieldset_grid.phtml';

    /**
     * @var \Magento\Catalog\Block\Adminhtml\Category\Tabs\Product
     */
    protected $blockGrid;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Hexamarvel\FlexibleForm\Model\FieldSetFactory $fieldSetFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Retrieve instance of grid block
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                \Hexamarvel\FlexibleForm\Block\Adminhtml\Field\Tab\DisplayTabFields::class,
                'tab.field.grid'
            );
        }
        return $this->blockGrid;
    }

    /**
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }

    /**
     * @return string
     */
    public function getNotice()
    {
        if ($this->getRequest()->getParam('id', false)) {
            return;
        } else {
            return __('Please save the form to add Fields.');
        }
    }
}
