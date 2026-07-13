<?php
declare(strict_types=1);

namespace Ecommerce66\Brands\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface BrandDetailsRepositoryInterface
{

    /**
     * Save brand_details
     * @param \Ecommerce66\Brands\Api\Data\BrandDetailsInterface $brandDetails
     * @return \Ecommerce66\Brands\Api\Data\BrandDetailsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Ecommerce66\Brands\Api\Data\BrandDetailsInterface $brandDetails
    );

    /**
     * Retrieve brand_details
     * @param string $brandDetailsId
     * @return \Ecommerce66\Brands\Api\Data\BrandDetailsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($brandDetailsId);

    /**
     * Retrieve brand_details matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ecommerce66\Brands\Api\Data\BrandDetailsSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete brand_details
     * @param \Ecommerce66\Brands\Api\Data\BrandDetailsInterface $brandDetails
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Ecommerce66\Brands\Api\Data\BrandDetailsInterface $brandDetails
    );

    /**
     * Delete brand_details by ID
     * @param string $brandDetailsId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($brandDetailsId);
}
