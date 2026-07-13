<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Block\Adminhtml\Field\Tab\Renderer;

use Magento\Framework\DataObject;

class Fieldset extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Hexamarvel\FlexibleForm\Model\FieldSetFactory
     */
    protected $fieldSetFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Hexamarvel\FlexibleForm\Model\FieldSetFactory $fieldSetFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Hexamarvel\FlexibleForm\Model\FieldSetFactory $fieldSetFactory,
        array $data = []
    ) {
        $this->fieldSetFactory = $fieldSetFactory;
        parent::__construct($context, $data);
    }

    /**
     * get category name
     * @param  DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $fieldSetId = $row->getFieldset();
        if ($fieldSetId) {
            return $this->fieldSetFactory->create()->load($fieldSetId)->getTitle();
        }

        return 'Default';
    }
}
