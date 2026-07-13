<?php

namespace Swissup\SeoTemplates\Plugin\Model;

use Magento\Catalog\Model\AbstractModel;
use Swissup\SeoTemplates\Model\SeodataBuilder;
use Swissup\SeoTemplates\Model\Filter\Storefront\Filter as StorefrontFilter;

abstract class AbstractPlugin
{
    /**
     * @var SeodataBuilder
     */
    protected $seodataBuilder;

    /**
     * @var \Swissup\SeoTemplates\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @var StorefrontFilter
     */
    protected $storefrontFilter;

    /**
     * @param SeodataBuilder                          $seodataBuilder
     * @param \Swissup\SeoTemplates\Helper\Data       $helper
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     */
    public function __construct(
        SeodataBuilder $seodataBuilder,
        \Swissup\SeoTemplates\Helper\Data $helper,
        \Magento\Framework\Filter\FilterManager $filterManager,
        StorefrontFilter $storefrontFilter
    ) {
        $this->seodataBuilder = $seodataBuilder;
        $this->helper = $helper;
        $this->filterManager = $filterManager;
        $this->storefrontFilter = $storefrontFilter;
    }

    /**
     * Update metadata for for $entity with generated metadata
     *
     * @param  AbstractModel $entity
     * @return void
     */
    protected function updateMetadata(AbstractModel $entity)
    {
        $metadataKeys = ['meta_title', 'meta_description', 'meta_keywords', 'h1_tag'];
        foreach ($metadataKeys as $key) {
            $entityMetadata = trim($entity->getData($key) ?: '');
            if ($entityMetadata               // entity metadata is not empty
                && !$this->helper->isForced() // AND force generated data disabled
            ) {
                // skip
                continue;
            }

            $value = $this->seodataBuilder->getValidatedByKey($key, $entity);
            $value = $this->storefrontFilter->filter($value);
            if ($value) {
                $entity->setData($key, html_entity_decode($value, ENT_NOQUOTES));
            }
        }
    }

    /**
     * Optimize metadata. Truncate it to fit max length settings.
     *
     * @param  AbstractModel $entity
     * @return void
     */
    protected function optimizeMetadata(AbstractModel $entity)
    {
        $dataNames = ['meta_title', 'meta_description'];
        foreach ($dataNames as $name) {
            $value = $entity->getData($name);
            if (!$value) {
                continue;
            }

            $etc = $this->helper->getOptimizeEtc($name);
            $length = $this->helper->getOptimizeLength($name);
            $value = $this->filterManager->truncate(
                $value,
                ['length' => $length, 'breakWords' => false, 'etc' => $etc]
            );
            $entity = $entity->setData($name, $value);
        }
    }
}
