<?php
declare(strict_types=1);

namespace Ceb\Installments\Model;

use Ceb\Installments\Api\Data\InstallmentInterface;
use Ceb\Installments\Api\Data\InstallmentInterfaceFactory;
use Ceb\Installments\Api\Data\InstallmentSearchResultsInterfaceFactory;
use Ceb\Installments\Api\InstallmentRepositoryInterface;
use Ceb\Installments\Model\ResourceModel\Installment as ResourceInstallment;
use Ceb\Installments\Model\ResourceModel\Installment\CollectionFactory as InstallmentCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class InstallmentRepository implements InstallmentRepositoryInterface
{

    /**
     * @var ResourceInstallment
     */
    protected $resource;

    /**
     * @var InstallmentInterfaceFactory
     */
    protected $installmentFactory;

    /**
     * @var InstallmentCollectionFactory
     */
    protected $installmentCollectionFactory;

    /**
     * @var Installment
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;


    /**
     * @param ResourceInstallment $resource
     * @param InstallmentInterfaceFactory $installmentFactory
     * @param InstallmentCollectionFactory $installmentCollectionFactory
     * @param InstallmentSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceInstallment $resource,
        InstallmentInterfaceFactory $installmentFactory,
        InstallmentCollectionFactory $installmentCollectionFactory,
        InstallmentSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->installmentFactory = $installmentFactory;
        $this->installmentCollectionFactory = $installmentCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(InstallmentInterface $installment)
    {
        try {
            $this->resource->save($installment);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the installment: %1',
                $exception->getMessage()
            ));
        }
        return $installment;
    }

    /**
     * @inheritDoc
     */
    public function get($installmentId)
    {
        $installment = $this->installmentFactory->create();
        $this->resource->load($installment, $installmentId);
        if (!$installment->getId()) {
            throw new NoSuchEntityException(__('Installment with id "%1" does not exist.', $installmentId));
        }
        return $installment;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->installmentCollectionFactory->create();
        
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
    public function delete(InstallmentInterface $installment)
    {
        try {
            $installmentModel = $this->installmentFactory->create();
            $this->resource->load($installmentModel, $installment->getInstallmentId());
            $this->resource->delete($installmentModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Installment: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($installmentId)
    {
        return $this->delete($this->get($installmentId));
    }
}

