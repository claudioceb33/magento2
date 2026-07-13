<?php

namespace Swissup\RichSnippets\Block;

class Breadcrumbs extends LdJson
{
    /**
     * @var \Swissup\RichSnippets\Plugin\Breadcrumbs
     */
    protected $breadcrumbs;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $catalogData;

    /**
     * Constructor
     *
     * @param \Swissup\RichSnippets\Plugin\Breadcrumbs         $breadcrumbs
     * @param \Magento\Catalog\Helper\Data                     $catalogData
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array                                            $data
     */
    public function __construct(
        \Swissup\RichSnippets\Plugin\Breadcrumbs $breadcrumbs,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->breadcrumbs = $breadcrumbs;
        $this->catalogData = $catalogData;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getLdJson()
    {
        if (!$this->getStoreConfig('richsnippets/breadcrumbs/enabled')) {
            return '';
        }

        $itemsList = [];
        $position = 1;
        foreach ($this->getCrumbs() as $crumbInfo) {
            if (isset($crumbInfo['link']) && !empty($crumbInfo['link'])) {
                $itemsList[] = [
                    '@type' => 'ListItem',
                    'position' => $position,
                    'item' => [
                        '@id' => $crumbInfo['link'],
                        'name' => $this->escapeHtml($crumbInfo['label'])
                    ]
                ];
                $position++;
            }
        }

        if (empty($itemsList)) {
            return '';
        }

        $ldArray = [
            '@context' => 'http://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemsList
        ];

        return $this->prepareJsonString($ldArray);
    }

    /**
     * Get breadcrumbs
     *
     * @return array
     */
    protected function getCrumbs()
    {
        $crumbs = $this->breadcrumbs->getCrumbs(); // get crumbs from plugin
        if (empty($crumbs)) {
            // seems like no one called addCrumb method
            // get breadcrumbs from catalog helper
            $crumbs['home'] = [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $this->_storeManager->getStore()->getBaseUrl()
            ];
            $crumbs += $this->catalogData->getBreadcrumbPath();
        }

        return $crumbs;
    }
}
