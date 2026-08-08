<?php

namespace Ceb\AdminGrid\Plugin;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\View\Element\UiComponent\DataCeb\SearchResult;
use Ceb\AdminGrid\Ui\AdminGrid\DataCeb\Category\ListingDataCeb as CategoryDataCeb;

class AddAttributesToUiDataCeb
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var ProductMetadataInterface */
    private $productMetadata;

    /**
     * Constructor
     *
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        ProductMetadataInterface $productMetadata
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->productMetadata = $productMetadata;
    }

    /**
     * Get Search Result after plugin
     *
     * @param CategoryDataCeb $subject
     * @param SearchResult $result
     * @return SearchResult
     */
    public function afterGetSearchResult(CategoryDataCeb $subject, SearchResult $result)
    {
        if ($result->isLoaded()) {
            return $result;
        }

        $edition = $this->productMetadata->getEdition();
        $column = 'entity_id';
        if ($edition == 'Enterprise') {
            $column = 'row_id';
        }

        $attribute = $this->attributeRepository->get('catalog_category', 'name');

        $result->getSelect()->joinLeft(
            ['modulenamegridname' => $attribute->getBackendTable()],
            'modulenamegridname.' . $column . ' = main_table.' . $column . ' AND modulenamegridname.attribute_id = '
            . $attribute->getAttributeId(),
            ['name' => 'modulenamegridname.value']
        );

        $result->getSelect()->where('modulenamegridname.value LIKE "B%"');

        return $result;
    }
}
