<?php

namespace Swissup\SeoTemplates\Model;

use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Swissup\SeoTemplates\Model\ResourceModel\Template\CollectionFactory as TemplateCollectionFactory;

class Generator extends \Magento\Framework\DataObject
{
    protected Template $template;
    protected TemplateCollectionFactory $templateCollectionFactory;
    protected ResourceModel\Log $resourceLog;
    protected StoreManagerInterface $storeManager;
    protected LoggerInterface $logger;
    protected Generator\Assistant $assistant;

    private $updater;

    public function __construct(
        Template $template,
        TemplateCollectionFactory $templateCollectionFactory,
        ResourceModel\Log $resourceLog,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        Generator\Assistant $assistant,
        array $data = []
    ){
        $this->template = $template;
        $this->templateCollectionFactory = $templateCollectionFactory;
        $this->resourceLog = $resourceLog;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->assistant = $assistant;

        parent::__construct($data);
    }

    /**
     * Get page size
     *
     * @return int
     */
    public function getPageSize()
    {
        if (!$this->hasData('page_size')) {
            $this->setData('page_size', 33);
        }

        return $this->getData('page_size');
    }

    /**
     * Get current page
     *
     * @return int
     */
    public function getCurPage()
    {
        if (!$this->hasData('cur_page')) {
            $this->setData('cur_page', 1);
        }

        return $this->getData('cur_page');
    }

    /**
     * Get entityt type to process
     *
     * @return int
     */
    public function getEntityType()
    {
        if (!$this->hasData('entity_type')) {
            $this->setData('entity_type', 0);
        }

        return $this->getData('entity_type');
    }

    /**
     * Generate metadat using templates
     *
     * @param  array $entityIds
     * @return $this
     */
    public function generate($entityIds = [])
    {
        $type = $this->getEntityType();
        $templateCollection = $this->getTemplateCollection()
            ->addFieldToFilter('entity_type', [
                'eq' => $this->getEntityType()
            ]);
        $templates = $templateCollection->getItems();
        if (count($templates) < 1) {
            $this->setProcessedItems(NULL);
            $this->setNextPage(false);

            return $this;
        }

        $entityCollection = $this->getCollectionForEntityType($type)
            ->setPage($this->getCurPage(), $this->getPageSize());
        if ($entityIds) {
            $entityCollection->addFieldToFilter('entity_id', ['in' => $entityIds]);
        }

        $this->setProcessedItems(0);
        $log = [];
        // backup current store
        $backupCurrentStore = $this->storeManager->getStore();
        foreach ($entityCollection as $entity) {
            $id = $entity->getId();
            foreach ($this->storeManager->getStores(true) as $store) {
                try {
                    $entity->unsetData();
                    $entity->setStoreId($store->getId())->load($id);
                } catch (\Exception $e) {
                    $this->logException($e, $type, $entity, $store);
                    continue;
                }

                // skip store if product is not visible in store
                if ($entity->getVisibility() == ProductVisibility::VISIBILITY_NOT_VISIBLE) {
                    continue;
                }

                // change current store (required for configurable products)
                if ($store->getId() == 0) {
                    // for All Store View use default Default Store View
                    $this->storeManager->setCurrentStore(
                        $this->storeManager->getDefaultStoreView()
                    );
                } else {
                    // else use specified store view
                    $this->storeManager->setCurrentStore($store);
                }

                $seodata = false;
                foreach ($templates as $template) {
                    // skip template if it does not assigned to store
                    if (!in_array($store->getId(), $template->getStoreId())) {
                        continue;
                    }

                    // skip template if if does not meet conditions
                    if (!$template->getConditions()->validate($entity)) {
                        continue;
                    };

                    // load seo data if it is not loaded yet
                    if (!$seodata) {
                        $seodata = $this->assistant->getSeodata($entity);
                    }

                    // generate SEO data using template
                    $generatedValue = $this->assistant
                        ->updateSeodataFromTemplate($seodata, $template, $entity);

                    // add log record
                    $log[] = [
                        'template_id' => $template->getId(),
                        'entity_id' => $entity->getId(),
                        'store_id' => $store->getId(),
                        'generated_value' => $generatedValue
                    ];
                }

                // Save generated seo data into DB
                if ($seodata) {
                    $seodata->save();
                }
            }

            $this->setProcessedItems($this->getProcessedItems() + 1);
        }

        // Save log into DB
        $this->resourceLog->insertData($log);

        $this->storeManager->setCurrentStore($backupCurrentStore);

        if ($this->getCurPage() >= $entityCollection->getLastPageNumber()) {
            $this->setNextPage(false);
        } else {
            $this->setNextPage(
                $this->getCurPage() < 1
                ? 2
                : ($this->getCurPage() + 1)
            );
        }

        return $this;
    }

    /**
     * Get collection for specific entity type
     *
     * @param  int $entityType
     * @return mixed
     */
    public function getCollectionForEntityType($entityType)
    {
        if ($this->hasData('collectionFactories')) {
            $typeCode = $this->template->getEntityTypeCode($entityType);
            $factory = $this->getData("collectionFactories/{$typeCode}");
            if ($factory) {
                return $factory->create();
            }
        }

        return null;
    }

    /**
     * Get entity type name
     *
     * @param  int $entityType
     * @return mixed
     */
    public function getEntityTypeName($entityType, $translateLabel = true)
    {
        return $this->template->getEntityTypeName($entityType, $translateLabel);
    }

    /**
     * Get SEO Templates collection (only active)
     *
     * @return ResourceModel\Template\Collection
     */
    public function getTemplateCollection()
    {
        $collection = $this->templateCollectionFactory->create()
            ->addFieldToFilter('status', ['eq' => 1])
            ->setOrder('priority', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
        return $collection;
    }

    /**
     * Available entity types for template
     *
     * @return array
     */
    public function getAvailableEntityTypes($translateLabel = true)
    {
        return $this->template->getAvailableEntityTypes($translateLabel);
    }

    /**
     * Clear templates logs where entity_type in $entityType
     *
     * @param  array $entityTypes
     * @return $this
     */
    public function claerTemplatesLogs($entityTypes = [], $entityIds = [])
    {
        $collection = $this->getTemplateCollection();
        $collection->addFieldToFilter('entity_type', ['in' => $entityTypes]);
        foreach ($collection as $template) {
            $template->clearLog($entityIds);
        }

        return $this;
    }

    /**
     * Clear generated data for emtity types in $entityTypes
     *
     * @param  array  $entityTypes
     * @param  array  $entityIds
     * @return $this
     */
    public function clearGeneratedData($entityTypes = [], $entityIds = [])
    {
        $seodata = $this->assistant->getSeodata();
        $seodata->deleteGenerated($entityTypes, $entityIds);
        return $this;
    }

    /**
     * Log exception into system.log
     *
     * @param  \Exception                    $exception
     * @param  string                        $entityType
     * @param  \Magento\Framework\DataObject $entity
     * @param  \Magento\Store\Model\Store    $store
     */
    protected function logException(
        \Exception $exception,
        $entityType,
        \Magento\Framework\DataObject $entity,
        \Magento\Store\Model\Store $store
    ) {
        $msg = $this->hasData('exceptions')
            ? $this->getData('exceptions/load_failed')
            : '';
        $this->logger->critical(
            __(
                $msg,
                $this->getEntityTypeName($entityType),
                $entity->getId(),
                $store->getId()
            ),
            [
                'details' => $exception->getMessage()
            ]
        );
    }
}
