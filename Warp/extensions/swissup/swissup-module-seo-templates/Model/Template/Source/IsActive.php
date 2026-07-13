<?php
namespace Swissup\SeoTemplates\Model\Template\Source;

use Magento\Framework\Data\OptionSourceInterface;

class IsActive implements OptionSourceInterface
{
    /**
     * @var \Swissup\SeoTemplates\Model\Template
     */
    protected $seoTemplate;

    /**
     * Constructor
     *
     * @param \Swissup\SeoTemplates\Model\Template $seoTemplate
     */
    public function __construct(
        \Swissup\SeoTemplates\Model\Template $seoTemplate
    ) {
        $this->seoTemplate = $seoTemplate;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->seoTemplate->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
