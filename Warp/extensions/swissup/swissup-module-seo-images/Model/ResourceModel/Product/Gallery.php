<?php

namespace Swissup\SeoImages\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\Gallery as CatalogGallery;

class Gallery extends CatalogGallery
{
    /**
     * @param  string $fileName
     * @return array
     */
    public function getProductIds($fileName)
    {
        $linkField = $this->metadata->getLinkField();
        $mainTableAlias = $this->getMainTableAlias();
        $select = $this->getConnection()->select()->from(
            [$mainTableAlias => $this->getMainTable()],
            []
        )->joinInner(
            ['entity' => $this->getTable(self::GALLERY_VALUE_TO_ENTITY_TABLE)],
            $mainTableAlias . '.value_id = entity.value_id',
            [$linkField]
        )->where(
            $mainTableAlias . '.value = ?', $fileName
        )->where(
            $mainTableAlias . '.disabled = 0'
        );

        return $this->getConnection()->fetchCol($select);
    }
}
