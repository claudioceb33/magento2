<?php

namespace Swissup\SeoUrls\Model\Filter;

abstract class AbstractPredefined extends Attribute
{
    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        $store = $this->helper->getCurrentStore();
        $labelDataKey = 'label_store_' . $store->getId();
        if (!$this->hasData($labelDataKey)) {
            $filter = $this->getLayerFilter();
            $inUrlLabel = $this->seoAttribute->getInUrlLabel($filter);
            $this->setData(
                $labelDataKey,
                $inUrlLabel
                    ? $inUrlLabel
                    : $this->getDefaultLabel()
            );
        }

        return $this->getData($labelDataKey);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        if (!$this->hasData('options')) {
            $filter = $this->getLayerFilter();
            $options = [];
            foreach ($this->getDefaultOptions() as $value => $defaultLabel) {
                $option = new \Magento\Framework\DataObject([
                    'value' => $value
                ]);
                $inUrlValue = $this->seoAttribute->getInUrlValue($filter, $option);
                $options[$value] = $inUrlValue !== null
                    ? $inUrlValue
                    : $defaultLabel;
            }

            $this->setData('options', $options);
        }

        return $this->getData('options');
    }

    /**
     * Get default labels for filter when there are no user specified value
     *
     * @return string
     */
    abstract protected function getDefaultLabel();

    /**
     * Get default options for filter when there are no user specified
     *
     * @return array
     */
    abstract protected function getDefaultOptions();
}
