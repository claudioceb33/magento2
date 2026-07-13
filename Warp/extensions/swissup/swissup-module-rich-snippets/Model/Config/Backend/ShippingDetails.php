<?php

namespace Swissup\RichSnippets\Model\Config\Backend;

class ShippingDetails extends AbstractDynamicRows
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
                || !array_key_exists('country', $row)
                || !array_key_exists('method', $row)
                || !array_key_exists('handling', $row)
                || !array_key_exists('transit', $row)
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
                || !array_key_exists('country', $row)
                || !array_key_exists('method', $row)
                || !array_key_exists('handling', $row)
                || !array_key_exists('transit', $row)
            ) {
                continue;
            }

            $country = $row['country'];
            $method = $row['method'];
            unset($row['country']);
            unset($row['method']);
            $result["{$country}::{$method}"] = $row;
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
            foreach ($value as $countryMethod => $details) {
                $data[$countryMethod] = $details;
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
        foreach ($value as $countryMethod => $details) {
            list($country, $method) = explode('::', $countryMethod, 2);
            $resultId = $this->mathRandom->getUniqueHash('_');
            $result[$resultId] = [
                'country' => $country,
                'method' => $method,
                'handling' => $details['handling'],
                'transit' => $details['transit']
            ];
        }

        return $result;
    }
}
