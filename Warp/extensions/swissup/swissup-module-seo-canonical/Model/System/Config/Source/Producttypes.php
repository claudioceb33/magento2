<?php
namespace Swissup\SeoCanonical\Model\System\Config\Source;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

class Producttypes implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    private $productTypes;

    private array $allowedTypes;

    /**
     * @param \Magento\Catalog\Model\Product\Type $productTypes
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Type $productTypes
    ) {
        $this->productTypes = $productTypes;
        $this->allowedTypes = [
            Configurable::TYPE_CODE, Bundle::TYPE_CODE, Grouped::TYPE_CODE
        ];
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->productTypes->getOptions();
        foreach ($options as $key => $option) {
            if (!in_array($option['value'], $this->allowedTypes)) {
                unset($options[$key]);
            }
        }

        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $result = [];
        foreach ($this->toOptionArray() as $item) {
            $result[$item['value']] = $item['label'];
        }

        return $result;
    }
}
