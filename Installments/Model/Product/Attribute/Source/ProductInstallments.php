<?php

namespace Ceb\Installments\Model\Product\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Ceb\Installments\Model\InstallmentRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;

class ProductInstallments extends AbstractSource implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var InstallmentRepository
     */
    private $installmentRepository;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /**
     * ProductInstallments constructor.
     *
     * @param InstallmentRepository $installmentRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        InstallmentRepository $installmentRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        $this->installmentRepository = $installmentRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        $options = [];
        $options[] = [
            'value' => '0',
            'label' => 'No'
        ];

        // Fetch options from the Installment model
        $criteria = $this->searchCriteriaBuilder->create();
        $installments = $this->installmentRepository->getList($criteria);

        foreach ($installments->getItems() as $installment) {
            $options[] = [
                'value' => $installment->getInstallmentId(),
                'label' => $installment->getName(),
            ];
        }

        return $options;
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
