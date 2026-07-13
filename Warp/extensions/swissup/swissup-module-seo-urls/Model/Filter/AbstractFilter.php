<?php

namespace Swissup\SeoUrls\Model\Filter;

abstract class AbstractFilter extends \Magento\Framework\DataObject
{

    /**
     * @var \Swissup\SeoUrls\Helper\Data
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param \Swissup\SeoUrls\Helper\Data     $helper
     * @param array                            $data
     */
    public function __construct(
        \Swissup\SeoUrls\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($data);
    }

    /**
     * Get lable (seo friendly) for filter by attribute
     *
     * @return string
     */
    abstract public function getLabel();

    /**
     * Get options (seo friendly) for filter by attribute
     *
     * @return array
     */
    abstract public function getOptions();

    /**
     * Get sort order for filter by attribute
     *
     * @return int
     */
    abstract public function getSortOrder();

}
