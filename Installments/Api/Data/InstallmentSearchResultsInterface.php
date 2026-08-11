<?php
declare(strict_types=1);

namespace Ceb\Installments\Api\Data;

interface InstallmentSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Installment list.
     * @return \Ceb\Installments\Api\Data\InstallmentInterface[]
     */
    public function getItems();

    /**
     * Set title list.
     * @param \Ceb\Installments\Api\Data\InstallmentInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

