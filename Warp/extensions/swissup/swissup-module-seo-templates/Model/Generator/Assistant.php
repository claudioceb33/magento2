<?php

namespace Swissup\SeoTemplates\Model\Generator;

use Magento\Catalog\Model\AbstractModel;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Swissup\SeoTemplates\Model\Seodata;
use Swissup\SeoTemplates\Model\ResourceModel\Seodata\CollectionFactory;
use Swissup\SeoTemplates\Model\Template;

class Assistant
{
    private $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function getSeodata(
        AbstractModel $entity = null
    ): Seodata {
        $collection = $this->collectionFactory->create();
        if (!$entity) {
            return $collection->getNewEmptyItem();
        }

        $entityId = $entity->getId();
        $type = $this->getEntityType($entity);
        $storeId = $entity->getStoreId();
        $seodata =  $collection
            ->addFieldToFilter('entity_id', $entityId)
            ->addFieldToFilter('entity_type', $type)
            ->addFieldToFilter('store_id', $storeId)
            ->getFirstItem()
            ->unserialize();

        if (!$seodata->getId()) {
            $seodata->setData([
                'entity_id' => $entityId,
                'entity_type' => $type,
                'store_id' => $storeId
            ]);
        }

        return $seodata;
    }

    public function updateSeodataFromTemplate(
        Seodata $seodata,
        Template $template,
        AbstractModel $entity
    ) {
        $dataNameId = $template->getDataName();
        // code of metadata we want to update with template
        $dataCode = $template->getDataNameCode($dataNameId);
        $meta = $seodata->getData('metadata');
        // get value of metadata by reference
        $value = &$meta[$dataCode];
        if (!is_array($value)) {
            // compatibility with data stored old way
            // the way data stored changed in version 1.7.0
            $value = $value ? ['value' => $value] : [];
        }

        $generated = (string)$template->generate($entity);
        if ($storefrontConditions = $template->getStorefrontConditions()) {
            // There are conditions possible to validate on storefront only
            if (!isset($value['conditional'])) {
                $value['conditional'] = [];
            }

            // Conditions for each template save separatly with priority.
            $value['conditional'][$template->getId()] = [
                'condition' => $storefrontConditions,
                'priority' => $template->getPriority(),
                'value' => $generated
            ];
        } else {
            // THere are no storefront conditions.
            $value['value'] = $generated;
        }

        $seodata->setData('metadata', $meta);

        return $generated;
    }

    static public function getEntityType(
        AbstractModel $entity
    ): int {
        switch ($entity::ENTITY) {
            case Category::ENTITY:
                return Template::ENTITY_TYPE_CATEGORY;
                break;

            case Product::ENTITY:
                $entityType = Template::ENTITY_TYPE_PRODUCT;
                break;

            default:
                $entityType = 0;
                break;
        }

        return $entityType;
    }

}
