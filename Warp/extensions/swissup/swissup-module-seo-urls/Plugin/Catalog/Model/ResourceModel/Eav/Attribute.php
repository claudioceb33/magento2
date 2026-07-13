<?php
/**
 * Plugin for \Magento\Catalog\Model\ResourceModel\Eav\Attribute
 */
namespace Swissup\SeoUrls\Plugin\Catalog\Model\ResourceModel\Eav;

class Attribute
{
    /**
     * @var \Swissup\SeoUrls\Model\ResourceModel\Attribute\Action
     */
    private $attributeAction;

    /**
     * @param \Swissup\SeoUrls\Model\ResourceModel\Attribute\Action $attributeAction
     */
    public function __construct(
        \Swissup\SeoUrls\Model\ResourceModel\Attribute\Action $attributeAction
    ) {
        $this->attributeAction = $attributeAction;
    }

    /**
     * Before plugin for afterSave method.
     * Save swissup[seoulr_label] data.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $subject
     */
    public function beforeAfterSave(
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $subject
    ) {
        // save labels
        if ($labels = $subject->getData('swissup/seourl_label')) {
            $this->attributeAction->updateInUrlLabels($subject, $labels);
        }

        // save options
        $decodedData = $this->decodeValuesData(
            (string)$subject->getData('swissup/values_serialized')
        );
        $urlValues = $decodedData->getData('swissup/seourl_value');
        if (!$urlValues) {
            return;
        }

        // $originalValues = $decodedData->getData('swissup/original_value');
        foreach ($urlValues as $valueId => $values) {
            $originalValue = $decodedData->getData(
                "swissup/original_value/{$valueId}"
            );
            if (isset($originalValue)) {
                $this->attributeAction->updateInUrlValues(
                    $subject->getId(),
                    $originalValue,
                    $values
                );
            }
        }

        // save advaced properties
        $data = [];
        if ($subject->hasData('seourl_add_nofollow')) {
            $data['is_nofollow'] = $subject->getData('seourl_add_nofollow');
        }

        if ($data) {
            $this->attributeAction->updateAdvacedProps($subject, $data);
        }
    }

    /**
     * Decode serialized attribute values string into DataObject
     *
     * @param  string $serializedValues
     * @return \Magento\Framework\DataObject
     */
    private function decodeValuesData(string $serializedValues)
    {
        $data = [];
        $decodedValues = json_decode($serializedValues, JSON_OBJECT_AS_ARRAY);
        if ($decodedValues) {
            foreach ($decodedValues as $decodedValue) {
                $value = [];
                parse_str($decodedValue, $value);
                $data = array_replace_recursive($data, $value);
            }
        }

        return new \Magento\Framework\DataObject($data);
    }
}
