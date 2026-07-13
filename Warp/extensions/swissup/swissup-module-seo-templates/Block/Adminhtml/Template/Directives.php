<?php

namespace Swissup\SeoTemplates\Block\Adminhtml\Template;

use Swissup\SeoTemplates\Model\Template;

class Directives extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->_template = 'directives.phtml';
        parent::__construct($context, $data);
    }

    /**
     * Is template type == product
     *
     * @return boolean
     */
    public function isTypeProduct()
    {
        $templateType = $this->registry->registry('seotemplates_template_type');
        return $templateType == Template::ENTITY_TYPE_PRODUCT;
    }

    /**
     * Is template type == category
     *
     * @return boolean
     */
    public function isTypeCategory()
    {
        $templateType = $this->registry->registry('seotemplates_template_type');
        return $templateType == Template::ENTITY_TYPE_CATEGORY;
    }
}