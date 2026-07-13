<?php
declare(strict_types=1);

namespace Ecommerce66\AiCore\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollectionFactory;

class ProductAttributes implements OptionSourceInterface
{
    /** @var string[] */
    private array $excluded = [
        'sku','name','price','special_price','minimal_price','small_image',
        'brand','url_key'
    ];

    private AttributeCollectionFactory $collectionFactory;

    /**
     * ProductAttributes constructor.
     *
     * @param AttributeCollectionFactory $collectionFactory
     */
    public function __construct(AttributeCollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('entity_type_id', 4); // catalog_product

        $options = [];
        foreach ($collection as $attr) {
            $code = (string)$attr->getAttributeCode();
            if (in_array($code, $this->excluded, true)) {
                continue;
            }
            $label = (string)($attr->getFrontendLabel() ?: $code);
            $options[] = ['value' => $code, 'label' => $label . ' (' . $code . ')'];
        }
        usort($options, fn($a,$b) => strcmp($a['label'],$b['label']));
        return $options;
    }
}
