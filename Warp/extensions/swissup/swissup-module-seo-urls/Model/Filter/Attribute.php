<?php

namespace Swissup\SeoUrls\Model\Filter;

class Attribute extends AbstractFilter
{

    /**
     * @var \Swissup\SeoUrls\Model\Attribute
     */
    protected $seoAttribute;

    /**
     * Constructor
     *
     * @param \Swissup\SeoUrls\Helper\Data     $helper
     * @param \Swissup\SeoUrls\Model\Attribute $seoAttribute
     * @param array                            $data
     */
    public function __construct(
        \Swissup\SeoUrls\Helper\Data $helper,
        \Swissup\SeoUrls\Model\Attribute $seoAttribute,
        array $data = []
    ) {
        $this->seoAttribute = $seoAttribute;
        parent::__construct($helper, $data);
    }

    /**
     * Get filtrable attribute used for filter
     *
     * @return null|
     */
    public function getLayerFilter()
    {
        return $this->getData('layer_filter');
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        $store = $this->helper->getCurrentStore();
        $labelDataKey = 'label_store_' . $store->getId();
        if (!$this->hasData($labelDataKey)) {
            $filter = $this->getLayerFilter();
            $this->setData(
                $labelDataKey,
                $this->seoAttribute->getStoreLabel($filter)
            );
        }

        return $this->getData($labelDataKey);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        $store = $this->helper->getCurrentStore();
        $optionsDataKey = 'options_store_' . $store->getId();
        if (!$this->hasData($optionsDataKey)) {
            $filter = $this->getLayerFilter();
            $options = $filter ? $this->seoAttribute->getOptions($filter) : [];
            $this->setData($optionsDataKey, array_filter($options));
        }

        return $this->getData($optionsDataKey);
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        if (!$this->hasData('sort_order')) {
            $filter = $this->getLayerFilter();
            if (isset($filter)) {
                $this->setData(
                    'sort_order',
                    $filter->getAttributeId() + 10000 * $filter->getPosition()
                );
            }
        }

        return $this->getData('sort_order');
    }
}
