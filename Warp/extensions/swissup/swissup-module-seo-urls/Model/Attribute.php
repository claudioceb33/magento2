<?php

namespace Swissup\SeoUrls\Model;

use Magento\Framework\DataObject;
use Swissup\SeoUrls\Model\Config\Source\NofollowStategy;

class Attribute
{
    /**
     * @var \Swissup\SeoUrls\Helper\Data
     */
    private $helper;

    /**
     * @var ResourceModel\Attribute\View
     */
    private $attributeView;

    /**
     * @param \Swissup\SeoUrls\Helper\Data $helper
     * @param ResourceModel\Attribute\View $attributeView
     */
    public function __construct(
        \Swissup\SeoUrls\Helper\Data $helper,
        ResourceModel\Attribute\View $attributeView
    ) {
        $this->helper = $helper;
        $this->attributeView = $attributeView;
    }

    /**
     * Get in-URL label for attribute
     *
     * @param  DataObject $attribute
     * @return string|null
     */
    public function getInUrlLabel(DataObject $attribute)
    {
        $storeId = $this->helper->getCurrentStore()->getId();
        $labels = $this->attributeView->getInUrlLabels($attribute);

        return isset($labels[$storeId])
            ? $labels[$storeId]['value']
            : (
                isset($labels[0])
                    ? $labels[0]['value']
                    : null
                );
    }

    /**
     * Get original store label of attribute converted into seo-friendly string
     *
     * @param  DataObject $attribute
     * @return string
     */
    public function getFallbackLabel(DataObject $attribute)
    {
        $storeId = $this->helper->getCurrentStore()->getId();
        $labels = $attribute->getStoreLabels();
        $label = isset($labels[$storeId])
            ? $labels[$storeId]
            : $attribute->getFrontendLabel();
        return $this->helper->getSeoFriendlyString($label);
    }

    /**
     * Get in-URL label for attribuet with fallback to converted orignal label
     *
     * @param  DataObject $attribute
     * @return string
     */
    public function getStoreLabel(DataObject $attribute)
    {
        $label = $this->getInUrlLabel($attribute);
        if (!$label) {
            $label = $this->getFallbackLabel($attribute);
        }

        return $label;
    }

    /**
     * Get in-URL value for attribute
     *
     * @param  DataObject $attribute
     * @param  DataObject $option
     * @return string|null
     */
    public function getInUrlValue(
        DataObject $attribute,
        DataObject $option
    ) {
        $storeId = $this->helper->getCurrentStore()->getId();
        $values = $this->attributeView->getInUrlValues(
            $attribute->getId(),
            $option->getValue()
        );

        return isset($values[$storeId])
            ? $values[$storeId]['url_value']
            : (
                isset($values[0])
                    ? $values[0]['url_value']
                    : null
                );
    }

    /**
     * Get original value of attribute converted into seo-friendly string
     *
     * @param  DataObject $option
     * @return string
     */
    public function getFallbackValue(DataObject $option)
    {
        return $this->helper->getSeoFriendlyString($option->getLabel());
    }

    /**
     * Get in-URL value for attribuet with fallback to converted orignal value
     *
     * @param  DataObject $attribute
     * @param  DataObject $option
     * @return
     */
    public function getStoreValue(
        DataObject $attribute,
        DataObject $option
    ) {
        $value = $this->getInUrlValue($attribute, $option);
        if (!$value) {
            $value = $this->getFallbackValue($option);
        }

        return $value;
    }

    /**
     * Check is nofollow allowed for attribute
     *
     * @param  DataObject $attribute
     * @return boolean
     * @deprecated 1.5.45
     */
    public function isNofollow(DataObject $attribute)
    {
        return $this->isNofollowForce($attribute);
    }

    public function isNofollowForce(DataObject $attribute): bool
    {
        $advanced = $this->attributeView->getAdvancedProps($attribute);
        $nofollowValue = (int)($advanced['is_nofollow'] ?? 0);

        return $nofollowValue === NofollowStategy::FORCE_NOFOLLOW;
    }

    public function isNofollowRemove(DataObject $attribute): bool
    {
        $advanced = $this->attributeView->getAdvancedProps($attribute);
        $nofollowValue = (int)($advanced['is_nofollow'] ?? 0);

        return $nofollowValue === NofollowStategy::REMOVE_NOFOLLOW;
    }

    public function getOptions(DataObject $attribute): array
    {
        $eavOptions = $attribute->getOptions() ?: [];

        $options = [];
        foreach ($eavOptions as $eavOption) {
            $value = $this->getStoreValue($attribute, $eavOption);
            if (in_array($value, $options)) {
                // this should not occur - poor options naming
                // there are multiple options with same label
                // concatenate option value
                $value .= '-' . $eavOption->getValue();
            }

            if ($this->helper->isModuleOutputEnabled('Smile_ElasticsuiteCatalog')) {
                // Integration with Smile_ElasticsuiteCatalog
                // Layered Navigation from Smile uses option label to filter products
                $key = (string)$eavOption->getLabel();
                if (isset($options[$key])) {
                    // this should not occur - poor options naming
                    $key .= $eavOption->getValue();
                }
            } else {
                // Magento Layered Navigation
                // It uses option value to filter products
                $key = $eavOption->getValue();
            }

            $options[$key] = $value;
        }

        return $options;
    }
}
