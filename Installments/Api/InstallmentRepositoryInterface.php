<?php
declare(strict_types=1);

namespace Ceb\Installments\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface InstallmentRepositoryInterface
{

    /**
     * Save Installment
     * @param \Ceb\Installments\Api\Data\InstallmentInterface $installment
     * @return \Ceb\Installments\Api\Data\InstallmentInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Ceb\Installments\Api\Data\InstallmentInterface $installment
    );

    /**
     * Retrieve Installment
     * @param string $installmentId
     * @return \Ceb\Installments\Api\Data\InstallmentInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($installmentId);

    /**
     * Retrieve Installment matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ceb\Installments\Api\Data\InstallmentSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Installment
     * @param \Ceb\Installments\Api\Data\InstallmentInterface $installment
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Ceb\Installments\Api\Data\InstallmentInterface $installment
    );

    /**
     * Delete Installment by ID
     * @param string $installmentId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($installmentId);
}

