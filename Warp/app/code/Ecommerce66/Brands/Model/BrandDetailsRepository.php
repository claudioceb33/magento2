<?php
declare(strict_types=1);

namespace Ecommerce66\Brands\Model;

use Ecommerce66\Brands\Api\BrandDetailsRepositoryInterface;
use Ecommerce66\Brands\Api\Data\BrandDetailsInterface;
use Ecommerce66\Brands\Api\Data\BrandDetailsInterfaceFactory;
use Ecommerce66\Brands\Api\Data\BrandDetailsSearchResultsInterfaceFactory;
use Ecommerce66\Brands\Model\ResourceModel\BrandDetails as ResourceBrandDetails;
use Ecommerce66\Brands\Model\ResourceModel\BrandDetails\CollectionFactory as BrandDetailsCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class BrandDetailsRepository implements BrandDetailsRepositoryInterface
{

    /**
     * @var ResourceBrandDetails
     */
    protected $resource;

    /**
     * @var BrandDetailsInterfaceFactory
     */
    protected $brandDetailsFactory;

    /**
     * @var BrandDetailsCollectionFactory
     */
    protected $brandDetailsCollectionFactory;

    /**
     * @var BrandDetails
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @param ResourceBrandDetails $resource
     * @param BrandDetailsInterfaceFactory $brandDetailsFactory
     * @param BrandDetailsCollectionFactory $brandDetailsCollectionFactory
     * @param BrandDetailsSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceBrandDetails $resource,
        BrandDetailsInterfaceFactory $brandDetailsFactory,
        BrandDetailsCollectionFactory $brandDetailsCollectionFactory,
        BrandDetailsSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->brandDetailsFactory = $brandDetailsFactory;
        $this->brandDetailsCollectionFactory = $brandDetailsCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(BrandDetailsInterface $brandDetails)
    {
        try {
            $this->resource->save($brandDetails);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the brandDetails: %1',
                $exception->getMessage()
            ));
        }
        return $brandDetails;
    }

    /**
     * @inheritDoc
     */
    public function get($brandDetailsId)
    {
        $brandDetails = $this->brandDetailsFactory->create();
        $this->resource->load($brandDetails, $brandDetailsId);
        if (!$brandDetails->getId()) {
            throw new NoSuchEntityException(__('brand_details with id "%1" does not exist.', $brandDetailsId));
        }
        return $brandDetails;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->brandDetailsCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(BrandDetailsInterface $brandDetails)
    {
        try {
            $brandDetailsModel = $this->brandDetailsFactory->create();
            $this->resource->load($brandDetailsModel, $brandDetails->getBrandDetailsId());
            $this->resource->delete($brandDetailsModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the brand_details: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($brandDetailsId)
    {
        return $this->delete($this->get($brandDetailsId));
    }
}
