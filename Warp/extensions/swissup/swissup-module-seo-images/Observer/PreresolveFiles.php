<?php

namespace Swissup\SeoImages\Observer;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Swissup\SeoImages\Model\FileResolver;
use Swissup\SeoImages\Model\ProductResolver;
use Swissup\SeoImages\Model\IndexFactory;

class PreresolveFiles implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Swissup\SeoImages\Helper\Data
     */
    protected $helper;

    /**
     * @var FileResolver
     */
    protected $fileResolver;

    /**
     * @var ProductResolver
     */
    protected $productResolver;

    /**
     * @var IndexFactory
     */
    protected $indexFactory;

    /**
     * @param \Swissup\SeoImages\Helper\Data $helper
     * @param FileResolver                   $fileResolver
     * @param ProductResolver                $productResolver
     * @param IndexFactory                   $indexFactory
     */
    public function __construct(
        \Swissup\SeoImages\Helper\Data $helper,
        FileResolver $fileResolver,
        ProductResolver $productResolver,
        IndexFactory $indexFactory
    ) {
        $this->helper = $helper;
        $this->fileResolver = $fileResolver;
        $this->productResolver = $productResolver;
        $this->indexFactory = $indexFactory;
    }

    /**
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $collection = $observer->getEvent()->getCollection();
        if (!$collection->getFlag(SetFlagPreresolveFiles::FLAG_NAME)) {
            return;
        }

        // unset flag
        $collection->getFlag(SetFlagPreresolveFiles::FLAG_NAME, null);

        if ($this->helper->isProduction()) {
            $id = $collection->getColumnValues('entity_id');
            $images = $this->indexFactory->create()->getCollection();
            $images->removeAllFieldsFromSelect();
            $images->addFieldToFilter('entity_id', ['in' => $id]);
            $images->distinct(true);
            $images->join(
                ['images' => $images->getTable('swissup_seoimages')],
                'images.original_file = main_table.file',
                ['original_file', 'target_file']
            );

            foreach ($images->getData() as $image) {
                $this->fileResolver->setTargetFile(
                    $image['original_file'],
                    $image['target_file']
                );
            }

            return;
        }

        $this->preloadImages($collection);
    }

    private function preloadImages(Collection $collection) {
        $productImages = $this->productResolver->preloadFromCollection($collection);
        $batchSize = 20;
        $index = 0;
        do {
            $images = array_slice($productImages, $index * $batchSize, $batchSize);
            if ($images) {
                $this->helper->preloadImages($images);
            }
            $index++;
        } while ($images);
    }
}
