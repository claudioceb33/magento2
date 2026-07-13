<?php

namespace Swissup\RichSnippets\Model\Config\Backend;

class OpeningHours extends AbstractDynamicRows
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
                || !array_key_exists('day_of_week', $row)
                || !array_key_exists('opens', $row)
                || !array_key_exists('closes', $row)
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
                || !array_key_exists('day_of_week', $row)
                || !array_key_exists('opens', $row)
                || !array_key_exists('closes', $row)
            ) {
                continue;
            }

            $dayOfWeek = $row['day_of_week'];
            unset($row['day_of_week']);
            $result[$dayOfWeek] = $row;
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
            foreach ($value as $dayOfWeek => $hours) {
                $data[$dayOfWeek] = $hours;
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
        foreach ($value as $dayOfWeek => $hours) {
            $resultId = $this->mathRandom->getUniqueHash('_');
            $result[$resultId] = [
                'day_of_week' => $dayOfWeek,
                'opens' => $hours['opens'],
                'closes' => $hours['closes']
            ];
        }

        return $result;
    }
}
