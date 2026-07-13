<?php

namespace Swissup\HreflangImportExport\Model\Export;

use Swissup\Hreflang\Model\ResourceModel\Category as ResourceCategory;
use Swissup\HreflangImportExport\Model\Export\CatalogCategory\CollectionFactory;
use Swissup\HreflangImportExport\Model\Export\CatalogCategory\AttributeCollectionProvider;

class CatalogCategory extends \Magento\ImportExport\Model\Export\AbstractEntity
{
    private AttributeCollectionProvider $attributeCollectionProvider;
    private CollectionFactory $entityCollectionFactory;
    private HeaderProvider $headerProvider;
    private ResourceCategory $resourceCategory;

    public function __construct(
        AttributeCollectionProvider $attributeCollectionProvider,
        CollectionFactory $collectionFactory,
        HeaderProvider $headerProvider,
        ResourceCategory $resourceCategory,
        Context $context,
        array $data = []
    ) {
        $this->attributeCollectionProvider = $attributeCollectionProvider;
        $this->entityCollectionFactory = $collectionFactory;
        $this->headerProvider = $headerProvider;
        $this->resourceCategory = $resourceCategory;

        parent::__construct(
            $context->getScopeConfig(),
            $context->getStoreManager(),
            $context->getCollectionFactory(),
            $context->getResourceColFactory(),
            $data
        );

        if (empty($this->_pageSize)) {
            $this->_pageSize = 1000;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityTypeCode()
    {
        return 'swissup_hreflang_catalog_categories';
    }

    /**
     * {@inheritdoc}
     */
    protected function _getEntityCollection()
    {
        return $this->entityCollectionFactory->create(
            $this->getAttributeCollection(),
            $this->_parameters
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _getHeaderColumns()
    {
        return $this->headerProvider->getHeaders(
            $this->getAttributeCollection(),
            $this->_parameters
        );
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function getAttributeCollection()
    {
        return $this->attributeCollectionProvider->get();
    }

    /**
     * Export given order link data
     *
     * @param \Magento\Cms\Model\Page $item
     * @return void
     */
    public function exportItem($item)
    {
        $row = $item->getData();
        $hreflangLinks = $this->resourceCategory->getHreflangLinks($item);
        $row['hreflang_links'] = implode(',', $hreflangLinks);

        $this->getWriter()->writeRow($row);
    }

    /**
     * Export process.
     *
     * @return string
     */
    public function export()
    {
        // create export file
        $writer = $this->getWriter();
        $writer->setHeaderCols($this->_getHeaderColumns());
        $this->_exportCollectionByPages($this->_getEntityCollection());

        return $writer->getContents();
    }
}
