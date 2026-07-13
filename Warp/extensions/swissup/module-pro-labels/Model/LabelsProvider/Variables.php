<?php

namespace Swissup\ProLabels\Model\LabelsProvider;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\Api\SimpleDataObjectConverter as Converter;
use Swissup\ProLabels\Helper\Data as Helper;

class Variables
{
    private Helper $helper;

    public function __construct(
        Helper $helper
    ) {
        $this->helper = $helper;
    }

    public function collect(
        array $labels,
        Product $product
    ): array {
        $variables = [];
        foreach ($this->getPlaceholders($labels) as $placeholder) {
            $directive = $this->getDirective($placeholder);
            if ($directive->getName() === 'attr' && $directive->getCode()) {
                $attribute = $product
                    ->getResource()
                    ->getAttribute($directive->getCode());
                if ($attribute) {
                    $variables[$placeholder] = $attribute
                        ->getFrontend()
                        ->getValue($product);
                }
            } else {
                $methodName = 'get'
                    . Converter::snakeCaseToUpperCamelCase($directive->getName())
                    . 'Value';
                $callback = [$this->helper, $methodName];
                if (is_callable($callback)) {
                    $variables[$placeholder] = call_user_func(
                        $callback,
                        $product
                    );
                }
            }
        }

        return $variables;
    }

    private function getPlaceholders(array $labels): array
    {
        // Flatten labels data array
        $flattenedLabels = array_merge([], ...array_values($labels));
        // Collect all texts
        $text = array_reduce($flattenedLabels, function ($t, $label) {
            return $t . $label->getText();
        }, '');

        // Remove all hex colors from label text.
        // Because text `<b style="color: #bbb">label #attr:sku#</b>` is too
        // triky to find predefined variables there.
        $text = preg_replace('/#([a-f0-9]{3}){1,2}\b/i', '', $text);
        // find all predefined variables
        preg_match_all('/#.+?#/', $text, $placeholders);

        return array_unique($placeholders[0] ?: []);
    }

    private function getDirective(string $placeholder): DataObject
    {
        $parts = explode(':', trim($placeholder, '#'));

        $directive = new DataObject(['name' => $parts[0] ?? '']);
        if ($directive->getName() === 'attr') {
            $directive->setCode($parts[1] ?? '');
            if (isset($parts[2])) {
                $directive->setType($parts[2]);
            }
        } elseif (isset($parts[1])) {
            $directive->setType($parts[2]);
        }

        return $directive;
    }
}
