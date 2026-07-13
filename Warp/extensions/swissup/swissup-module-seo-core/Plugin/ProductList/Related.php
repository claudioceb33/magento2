<?php

namespace Swissup\SeoCore\Plugin\ProductList;

use Magento\Catalog\Block\Product\ProductList\Related as BlockRelated;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

class Related
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->eventManager = $eventManager;
    }

    /**
     * @param  BlockRelated $subject
     * @param  Collection   $result
     * @return
     */
    public function afterGetItems(
        BlockRelated $subject,
        Collection $result
    ) {
        $this->eventManager->dispatch(
            'swissup_event_block_product_list_collection', [
                'collection' => $result
            ]
        );

        return $result;
    }
}
