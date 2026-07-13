<?php

namespace Swissup\HreflangImportExport\Model\Export;

use Swissup\Hreflang\Model\ResourceModel\Page as ResourcePage;
use Swissup\HreflangImportExport\Model\Export\CmsPage\CollectionFactory;
use Swissup\HreflangImportExport\Model\Export\CmsPage\AttributeCollectionProvider;

class CmsPage extends \Magento\ImportExport\Model\Export\AbstractEntity
{
    private AttributeCollectionProvider $attributeCollectionProvider;
    private CollectionFactory $entityCollectionFactory;
    private HeaderProvider $headerProvider;
    private ResourcePage $resourcePage;

    public function __construct(
        AttributeCollectionProvider $attributeCollectionProvider,
        CollectionFactory $collectionFactory,
        HeaderProvider $headerProvider,
        ResourcePage $resourcePage,
        Context $context,
        array $data = []
    ) {
        $this->attributeCollectionProvider = $attributeCollectionProvider;
        $this->entityCollectionFactory = $collectionFactory;
        $this->headerProvider = $headerProvider;
        $this->resourcePage = $resourcePage;

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
        return 'swissup_hreflang_cms_pages';
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
        $hreflangLinks = $this->resourcePage->getHreflangLinks($item);
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
