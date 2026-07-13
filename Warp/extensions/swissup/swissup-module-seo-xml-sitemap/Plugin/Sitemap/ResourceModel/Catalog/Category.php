<?php
/**
 * Plugins for methods in \Magento\Sitemap\Model\ResourceModel\Catalog\Category
 */
namespace Swissup\SeoXmlSitemap\Plugin\Sitemap\ResourceModel\Catalog;

use Magento\Sitemap\Model\Source\Product\Image\IncludeImage;
use Magento\Store\Model\Store;

class Category
{
    private $sitemapData;
    private $categoryResource;
    private $storeManager;
    private $imageAttributes;
    /**
     * @param \Swissup\SeoXmlSitemap\Helper\Data            $sitemapData
     * @param \Magento\Catalog\Model\ResourceModel\Category $categoryResource
     * @param \Magento\Store\Model\StoreManagerInterface    $storeManager
     */
    public function __construct(
        \Swissup\SeoXmlSitemap\Helper\Data $sitemapData,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ){
        $this->sitemapData = $sitemapData;
        $this->categoryResource = $categoryResource;
        $this->storeManager = $storeManager;
        $this->imageAttributes = ['image', 'thumbnail'];
    }

    /**
     * After method getCollection
     * (use around plugin for compatibility with Magento 2.1.x)
     *
     * Collect category images for site map
     *
     * @param  \Magento\Sitemap\Model\ResourceModel\Catalog\Category $subject
     * @param  callable                                              $proceed
     * @param  int                                                   $storeId
     * @return array
     */
    public function aroundGetCollection(
        \Magento\Sitemap\Model\ResourceModel\Catalog\Category $subject,
        callable $proceed,
        $storeId
    ) {
        $result = $proceed($storeId); // collection items - categories
        $imageIncludePolicy = $this->sitemapData->getCategoryImageIncludePolicy($storeId);
        if ($imageIncludePolicy == IncludeImage::INCLUDE_NONE
            || empty($result)
        ) {
            return $result;
        }

        // Join category images
        $extraData = $this->getExtraDataForCategories(
            $storeId,
            array_merge(['name'], $this->imageAttributes),
            $subject
        );

        foreach ($result as $categoryId => $category) {
            if (!isset($extraData[$categoryId])) {
                continue;
            }

            $data = $extraData[$categoryId];
            $category->setName($data['name']);
            $images = array_intersect_key($data, array_flip($this->imageAttributes));
            $imagesCollection = [];
            foreach (array_filter($images) as $code => $image) {
                $imageItem = new \Magento\Framework\DataObject(
                    [
                        'url' => $this->getCategoryImageUrl($image, $storeId)
                    ]
                );
                if ($imageIncludePolicy == IncludeImage::INCLUDE_BASE) {
                    if (empty($imagesCollection) || $code == 'image') {
                        $imagesCollection = [$imageItem];
                    }
                } elseif ($imageIncludePolicy == IncludeImage::INCLUDE_ALL) {
                    $imagesCollection[] = $imageItem;
                }
            }

            if ($imagesCollection) {
                // Determine thumbnail path
                $thumbnail = isset($images['thumbnail']) ? $images['thumbnail'] : '';
                if ($thumbnail) {
                    $thumbnail = $this->getCategoryImageUrl($thumbnail, $storeId);
                } else {
                    $thumbnail = $imagesCollection[0]->getUrl();
                }

                $category->setImages(
                    new \Magento\Framework\DataObject(
                        [
                            'collection' => $imagesCollection,
                            'title' => $category->getName(),
                            'thumbnail' => $thumbnail
                        ]
                    )
                );
            }
        }

        return $result;
    }

    /**
     * Get array with  from categories
     *
     * @param  int $storeId
     * @return array
     */
    protected function getExtraDataForCategories(
        $storeId,
        array $attributeList,
        \Magento\Sitemap\Model\ResourceModel\Catalog\Category $sitemapCategoryResource
    ) {
        $connection = $sitemapCategoryResource->getConnection();
        $select = $connection->select()->from(
            ['e' => $sitemapCategoryResource->getMainTable()],
            [$sitemapCategoryResource->getIdFieldName()]
        );

        foreach ($attributeList as $attributeCode) {
            $attribute = $this->categoryResource->getAttribute($attributeCode);
            $linkedField = $this->categoryResource->getLinkField();
            $tableAlias = 't1_' . $attributeCode;
            $select->joinLeft(
                [$tableAlias => $attribute->getBackend()->getTable()],
                "e.{$linkedField} = {$tableAlias}.{$linkedField}"
                . ' AND ' . $connection->quoteInto($tableAlias . '.store_id = ?', Store::DEFAULT_STORE_ID)
                . ' AND ' . $connection->quoteInto($tableAlias . '.attribute_id = ?', $attribute->getId()),
                []
            );

            // Global scope attribute value
            $columnValue = $tableAlias . '.value';
            if (!$attribute['is_global']) {
                $tableAlias2 = 't2_' . $attributeCode;
                $select->joinLeft(
                    [$tableAlias2 => $attribute->getBackend()->getTable()],
                    "e.{$linkedField} = {$tableAlias2}.{$linkedField}"
                    . ' AND ' . $connection->quoteInto($tableAlias2 . '.store_id = ?', $storeId)
                    . ' AND ' . $connection->quoteInto($tableAlias2 . '.attribute_id = ?', $attribute->getId()),
                    []
                );
                // Store scope attribute value
                $columnValue = $connection->getIfNullSql($tableAlias2 . '.value', $columnValue);
            }

            $select->columns([
               $attributeCode => $columnValue
            ]);
        }

        return $connection->fetchAssoc($select);
    }

    /**
     * Get URL for category image
     *
     * @param  string $image
     * @param  string $storeId
     * @return string
     */
    public function getCategoryImageUrl($image, $storeId)
    {
        $store = $this->storeManager->getStore($storeId);
        $url = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
            . 'catalog/category/'
            . $image;
        return $url;
    }
}
