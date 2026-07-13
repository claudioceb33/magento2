<?php

namespace Swissup\ProLabels\Model\LinkedItemsProcessor;

class UniqueProlabelsKey
{
    /**
     * Add memoization key for product to generate unique product labels.
     *
     * @param  array $items
     */
    public function process(array $items): void
    {
        foreach ($items as $item) {
            $product = $item['model'];
            $data = $product->getData('soldtogether_data');

            if (empty($data['promo_rule'])) {
                continue;
            }

            $id = $product->getId();
            $rule = $data['promo_rule'];
            $value = $data['promo_value'] ?? '';
            $product->setData('prolabels_data', [
                'memoization_key' => $id . '_' . $rule . '_' . $value
            ]);
        }
    }
}
