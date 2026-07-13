<?php

namespace Swissup\RichSnippets\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Context;
use Magento\Shipping\Model\Config\Source\Allmethods;

class ShippingMethod extends \Magento\Framework\View\Element\Html\Select
{
    private Allmethods $methods;

    public function __construct(
        Allmethods $methods,
        Context $context,
        array $data = []
    ) {
        $this->methods = $methods;
        parent::__construct($context, $data);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->methods->toOptionArray());
        }

        return parent::_toHtml();
    }
}
