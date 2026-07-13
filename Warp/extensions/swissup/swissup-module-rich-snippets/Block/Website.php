<?php
/**
 * Copyright © 2015 Swissup. All rights reserved.
 */
namespace Swissup\RichSnippets\Block;

class Website extends LdJson
{
    /**
     * @var Magento\CatalogSearch\Helper\Data
     */
    protected $catalogSearchHelper;

    /**
     * Constructor
     *
     * @param \Magento\CatalogSearch\Helper\Data $catalogSearchHelper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\CatalogSearch\Helper\Data $catalogSearchHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ){
        $this->catalogSearchHelper = $catalogSearchHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get array of values for website ld+json
     *
     * @return array
     */
    public function getValues()
    {
        $values = [];
        $website = $this->getStoreConfig('richsnippets/website');
        if (is_array($website)) {
            $values += $website;
        }

        return array_filter($values);
    }

    /**
     * {@inheritdoc}
     */
    public function getLdJson()
    {
        if (!$configValues = $this->getValues()) {
            return '';
        }

        // prepare general website info
        $keysMap = [
            'siteurl' => 'url',
            'sitename' => 'name'
        ];
        $ldArray = $this->remapArray($keysMap, $configValues);

        // prepare potential action
        $tagretUrl = $this->catalogSearchHelper->getResultUrl();
        $tagretUrl .= '?q={search_term_string}';
        $ldArray['potentialAction'] = [
            '@type' => 'SearchAction',
            'target' => $tagretUrl,
            'query-input' => 'required name=search_term_string'
        ];

        // add content schema and type
        if (empty($ldArray)) {
            return '';
        }

        $ldArray = ['@context' => 'http://schema.org', '@type' => 'WebSite']
            + $ldArray;

        return $this->prepareJsonString($ldArray);
    }
}
