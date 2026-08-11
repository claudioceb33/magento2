<?php
declare(strict_types=1);

namespace Ceb\Installments\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Ceb\Installments\Api\InstallmentRepositoryInterface;

class Data extends AbstractHelper
{
    /**
     * @var InstallmentRepositoryInterface
     */
    private $installmentRepository;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        InstallmentRepositoryInterface $installmentRepository
    ) {
        parent::__construct($context);
        $this->installmentRepository = $installmentRepository;
    }

    /**
     * Retrieve installment object by installment_id
     *
     * @param int $installmentId
     * @return \Ceb\Installments\Api\Data\InstallmentInterface|null
     */
    public function getInstallmentById($installmentId)
    {
        try {
            $installment = $this->installmentRepository->get($installmentId);
        } catch (\Exception $e) {
            // Handle the exception if needed
            $installment = null;
        }

        return $installment;
    }
}

