<?php

namespace Swissup\RichSnippets\Block\Adminhtml\Form\Field;

class DaysOfWeek extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * Flag whether to add group all option or no
     *
     * @var bool
     */
    protected $_addGroupAllOption = true;

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
            $days = [
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday',
                'Sunday',
                ['value' => 'Monday|Tuesday|Wednesday|Thursday|Friday', 'label' => 'Workweek (Monday - Friday)'],
                ['value' => 'Saturday|Sunday', 'label' => 'Weekend (Saturday, Sunday)']
            ];
            foreach ($days as $day) {
                $this->addOption(
                    $day['value'] ?? $day,
                    addslashes(__($day['label'] ?? $day)));
            }
        }

        return parent::_toHtml();
    }
}
