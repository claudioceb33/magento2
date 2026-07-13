<?php
declare(strict_types=1);

namespace Ecommerce66\Brands\Api\Data;

interface BrandDetailsSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get brand_details list.
     * @return \Ecommerce66\Brands\Api\Data\BrandDetailsInterface[]
     */
    public function getItems();

    /**
     * Set name list.
     * @param \Ecommerce66\Brands\Api\Data\BrandDetailsInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
