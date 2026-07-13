<?php

namespace Swissup\HreflangImportExport\Model\Export\CmsPage;

use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Framework\Data\Collection;
use Magento\ImportExport\Model\Export\Factory as CollectionFactory;

class AttributeCollectionProvider
{
    private Collection $collection;
    private AttributeFactory $attributeFactory;

    public function __construct(
        CollectionFactory $collectionFactory,
        AttributeFactory $attributeFactory
    ) {
        $this->collection = $collectionFactory->create(Collection::class);
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * @throws \Exception
     */
    public function get(): Collection
    {
        if (count($this->collection) === 0) {
            /** @var \Magento\Eav\Model\Entity\Attribute $pageIdAttribute */
            $pageIdAttribute = $this->attributeFactory->create();
            $pageIdAttribute->setId('page_id');
            $pageIdAttribute->setBackendType('int');
            $pageIdAttribute->setDefaultFrontendLabel('Page ID');
            $pageIdAttribute->setAttributeCode('page_id');
            $this->collection->addItem($pageIdAttribute);

            /** @var \Magento\Eav\Model\Entity\Attribute $hreflangLinksAttribute */
            $hreflangLinksAttribute = $this->attributeFactory->create();
            $hreflangLinksAttribute->setId('hreflang_links');
            $hreflangLinksAttribute->setBackendType('varchar');
            $hreflangLinksAttribute->setDefaultFrontendLabel('Hreflang links');
            $hreflangLinksAttribute->setAttributeCode('hreflang_links');
            $this->collection->addItem($hreflangLinksAttribute);
        }

        return $this->collection;
    }
}
