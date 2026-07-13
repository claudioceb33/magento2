<?php

namespace Swissup\RichSnippets\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Context;
use Magento\Directory\Model\Config\Source\Country as SourceCountry;

class Country extends \Magento\Framework\View\Element\Html\Select
{
    private SourceCountry $sourceCountry;

    public function __construct(
        SourceCountry $sourceCountry,
        Context $context,
        array $data = []
    ) {
        $this->sourceCountry = $sourceCountry;
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
            foreach ($this->sourceCountry->toOptionArray() as $option) {
                $this->addOption(
                    $option['value'],
                    addslashes($option['label']));
            }
        }

        return parent::_toHtml();
    }
}
