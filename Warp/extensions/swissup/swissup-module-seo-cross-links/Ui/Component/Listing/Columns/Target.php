<?php

namespace Swissup\SeoCrossLinks\Ui\Component\Listing\Columns;

use Swissup\SeoCrossLinks\Model\Link;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as CmsPageCollectionFactory;

class Target extends Column
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Collection[]|\Magento\Catalog\Model\ResourceModel\Product\Collection[]|\Magento\Cms\Model\ResourceModel\Page\Collection[]|mixed[]|array<int|string, mixed>
     */
    private $collections;
    /**
     * @var CategoryCollection
     */
    private $categoryCollection;

    /**
     * @var ProductCollection
     */
    private $productCollection;

    /**
     * @var CmsPageCollection
     */
    private $cmsPageCollection;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param SystemStore $systemStore
     * @param Escaper $escaper
     * @param array $components
     * @param array $data
     * @param string $storeKey
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        CmsPageCollectionFactory $cmsPageCollectionFactory,
        array $components = [],
        array $data = []
    ) {
        $this->collections = [
            LINK::URL_DESTINATION_CATEGORY => $categoryCollectionFactory->create(),
            LINK::URL_DESTINATION_PRODUCT => $productCollectionFactory->create(),
            LINK::URL_DESTINATION_CMS_PAGE => $cmsPageCollectionFactory->create(),
        ];
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $this->prepareEntityCollections($dataSource);
        $this->prepareExtraProductAttributes($dataSource);

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }

        return $dataSource;
    }

    /**
     * Get data
     *
     * @param array $item
     * @return string
     */
    private function prepareItem(array $item)
    {
        $result = '';

        if ($item['url_destination'] != Link::URL_DESTINATION_CUSTOM) {
            $collection = $this->collections[$item['url_destination']];
            $entity = $collection->getItemById($item['url_entity_id']);
        }

        if (is_null($item['url_destination'])) {
            $item['url_destination'] = intval($item['search_in']);
        }

        switch ($item['url_destination']) {
            case Link::URL_DESTINATION_CUSTOM:
                $result = __('Custom URL') . ': ' . $item['url_path'];
                break;
            case LINK::URL_DESTINATION_CATEGORY:
                $result = __('Category Page') . ': ' . $item['url_path'];
                break;
            case LINK::URL_DESTINATION_PRODUCT:
                $result = __('Product Page') . ': ' . $item['url_path'];
                break;
            case LINK::URL_DESTINATION_CMS_PAGE:
                $result = __('CMS Page') . ': ' . $item['url_path'];
                break;
        }

        return $result;
    }

    /**
     * @param  array  $dataSource
     * @return void
     */
    private function prepareEntityCollections(array $dataSource)
    {
        $result = [];

        if (!isset($dataSource['data']['items'])) {
            return $result;
        }

        foreach ($dataSource['data']['items'] as & $item) {
            if ($item['url_destination'] == Link::URL_DESTINATION_CUSTOM) {
                continue;
            }
            $result[$item['url_destination']][] = $item['url_entity_id'];
        }

        $mapping = [
            LINK::URL_DESTINATION_CATEGORY => 'entity_id',
            LINK::URL_DESTINATION_PRODUCT => 'entity_id',
            LINK::URL_DESTINATION_CMS_PAGE => 'page_id',
        ];
        foreach ($result as $urlType => $entityIds) {
            $collection = $this->collections[$urlType];
            $collection->addFieldToFilter($mapping[$urlType], ['in' => $entityIds]);

            switch ($urlType) {
                case LINK::URL_DESTINATION_CATEGORY:
                    $collection->addNameToResult();
                    break;
                case LINK::URL_DESTINATION_PRODUCT:
                    $collection->addAttributeToSelect('name');
                    break;
                case LINK::URL_DESTINATION_CMS_PAGE:
                    break;
            }
        }
    }

    /**
     * @param  array  $dataSource
     * @return array
     */
    private function prepareExtraProductAttributes(array $dataSource)
    {
        $result = [];

        if (!isset($dataSource['data']['items'])) {
            return $result;
        }

        foreach ($dataSource['data']['items'] as & $item) {
            if ($item['extra_attributes'] == null) {
                continue;
            }

            $result[] = $item['extra_attributes'];

        }

        return $result;
    }
}
