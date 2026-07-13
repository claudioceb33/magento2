<?php

namespace Swissup\SeoTemplates\Block\Adminhtml\Metadata;

use Magento\Framework\Registry;
use Magento\Store\Model\System\Store as Store;
use Swissup\SeoTemplates\Model\Generator\Assistant;
use Swissup\SeoTemplates\Model\SeodataBuilder;
use Swissup\SeoTemplates\Model\Template as SeoTemplate;
use Swissup\SeoTemplates\Model\ResourceModel\Seodata\CollectionFactory as SeodataCollectionFactory;

abstract class AbstractBlock extends \Magento\Framework\View\Element\Template
{
    protected Registry $registry;
    protected SeodataBuilder $seodataBuilder;
    protected SeodataCollectionFactory $seodataCollectionFactory;
    protected SeoTemplate $seoTemplate;
    protected Store $systemStore;

    /**
     * {@inheritdoc}
     */
    protected $_template = 'metadata.phtml';

    public function __construct(
        Registry $registry,
        SeodataBuilder $seodataBuilder,
        SeodataCollectionFactory $seodataCollectionFactory,
        SeoTemplate $seoTemplate,
        Store $systemStore,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->seodataBuilder = $seodataBuilder;
        $this->seodataCollectionFactory = $seodataCollectionFactory;
        $this->seoTemplate = $seoTemplate;
        $this->systemStore = $systemStore;

        parent::__construct($context, $data);
    }

    /**
     * Get courrent entity
     *
     * @return \Magento\Catalog\Model\AbstractModel
     */
    abstract public function getCurrentEntity();

    /**
     * Get seodataBuilder to get generated metadata
     *
     * @return SeodataBuilder
     */
    public function getSeodataBuilder()
    {
        return $this->seodataBuilder;
    }

    /**
     * Get available metadata names
     *
     * @return array
     */
    public function getAvailableDataNames()
    {
        return $this->seoTemplate->getAvailableDataNames();
    }

    /**
     * Get string code for seodata nane
     *
     * @param  int $seodataName
     * @return string
     */
    public function getDataNameCode($seodataName)
    {
        return $this->seoTemplate->getDataNameCode($seodataName);
    }

    /**
     * Get store vire label
     *
     * @param  string|int $storeId
     * @return string
     */
    public function getStoreViewLabel($storeId = '0')
    {
        if ($storeId == 0) {
            return __('All Store Views');
        }

        return $this->systemStore->getStoreName($storeId);
    }

    /**
     * Gte comma separated string of store view labels
     *
     * @return string
     */
    public function getStoreViewsList()
    {
        $entity = $this->getCurrentEntity();
        $label = [];
        $collection = $this->seodataCollectionFactory->create()
            ->addFilter('entity_type', Assistant::getEntityType($entity))
            ->addFilter('entity_id', $entity->getId())
            ->setOrder('store_id', 'ASC');
        foreach ($collection as $seodata) {
            $label[] = $this->getStoreViewLabel($seodata->getStoreId());
        }

        return implode(', ', $label);
    }
}
