<?php

namespace Ceb\Installments\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Ceb\Installments\Api\InstallmentRepositoryInterface;
use Ceb\Installments\Model\ResourceModel\Installment\CollectionFactory as InstallmentCollectionFactory;
use Magento\Directory\Model\Currency;

class Installments implements ArgumentInterface
{
    /**
     * @var InstallmentRepositoryInterface
     */
    private $installmentRepository;

    /**
     * @var InstallmentCollectionFactory
     */
    private $installmentCollectionFactory;

    /**
     * @var Currency
     */
    protected $currency;

    /**
     * Request-scope cache to avoid repeated DB loads for same installment id.
     *
     * @var array
     */
    private $installmentCache = [];

    /**
     * Installments constructor.
     * @param InstallmentRepositoryInterface $installmentRepository
     * @param InstallmentCollectionFactory $installmentCollectionFactory
     */
    public function __construct(
        InstallmentRepositoryInterface $installmentRepository,
        InstallmentCollectionFactory $installmentCollectionFactory,
        Currency $currency
    ) {
        $this->installmentRepository = $installmentRepository;
        $this->installmentCollectionFactory = $installmentCollectionFactory;
        $this->currency = $currency;
    }

    /**
     * Retrieve installment object by installment_id
     *
     * @param int $installmentId
     * @return \Ceb\Installments\Api\Data\InstallmentInterface|null
     */
    public function getInstallmentById($installmentId)
    {
        $installmentId = (int)$installmentId;
        if ($installmentId <= 0) {
            return null;
        }

        if (array_key_exists($installmentId, $this->installmentCache)) {
            return $this->installmentCache[$installmentId];
        }

        try {
            $installment = $this->installmentRepository->get($installmentId);
        } catch (\Exception $e) {
            // Handle the exception if needed
            $installment = null;
        }

        $this->installmentCache[$installmentId] = $installment;
        return $installment;
    }

    /**
     * Warm up installment cache with a single collection query.
     *
     * @param array $installmentIds
     * @return void
     */
    public function warmUpInstallments(array $installmentIds): void
    {
        $normalizedIds = [];
        foreach ($installmentIds as $installmentId) {
            $id = (int)$installmentId;
            if ($id <= 0 || array_key_exists($id, $this->installmentCache)) {
                continue;
            }
            $normalizedIds[] = $id;
        }

        $normalizedIds = array_values(array_unique($normalizedIds));
        if (empty($normalizedIds)) {
            return;
        }

        $collection = $this->installmentCollectionFactory->create();
        $collection->addFieldToFilter('installment_id', ['in' => $normalizedIds]);

        foreach ($collection as $item) {
            $this->installmentCache[(int)$item->getId()] = $item;
        }

        foreach ($normalizedIds as $id) {
            if (!array_key_exists($id, $this->installmentCache)) {
                $this->installmentCache[$id] = null;
            }
        }
    }

    public function getCurrentCurrencySymbol()
    {
        return $this->currency->getCurrencySymbol();
    } 

    public function getAmount($price, $rate, $qty) {
        if ($qty == 0) return '';
        $amount = ($price * $rate) / $qty;
        $amount = number_format($amount, 0, '', '.');
        $currencySymbol = $this->getCurrentCurrencySymbol();
        return $currencySymbol . ' ' . $amount;
    }
}
