<?php

namespace Swissup\RichSnippets\Model\Config\Backend;

class StructuredData extends AbstractDynamicRows
{
    /**
     * {@inheritdoc}
     */
    protected function isEncodedArrayFieldValue($value)
    {
        if (!is_array($value)) {
            return false;
        }
        unset($value['__empty']);
        foreach ($value as $row) {
            if (!is_array($row)
                || !array_key_exists('property', $row)
                || !array_key_exists('product_attribute', $row)
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function decodeArrayFieldValue(array $value)
    {
        $result = [];
        unset($value['__empty']);
        foreach ($value as $row) {
            if (!is_array($row)
                || !array_key_exists('property', $row)
                || !array_key_exists('product_attribute', $row)
            ) {
                continue;
            }

            $property = $row['property'];
            $result[$property] = $row['product_attribute'];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function serializeValue($value)
    {
        if (is_array($value)) {
            $data = [];
            foreach ($value as $property => $productAttribute) {
                $data[$property] = $productAttribute;
            }

            $value = $data;
        }

        return parent::serializeValue($value);
    }

    /**
     * {@inheritdoc}
     */
    protected function encodeArrayFieldValue(array $value)
    {
        $result = [];
        foreach ($value as $property => $productAttribute) {
            $resultId = $this->mathRandom->getUniqueHash('_');
            $result[$resultId] = [
                'property' => $property,
                'product_attribute' => $productAttribute
            ];
        }

        return $result;
    }
}
