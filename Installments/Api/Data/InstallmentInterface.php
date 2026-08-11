<?php
declare(strict_types=1);

namespace Ceb\Installments\Api\Data;

interface InstallmentInterface
{

    const INSTALLMENT_ID = 'installment_id';
    const NAME           = 'name';
    const INSTALLMENTS   = 'installments';
    const RATE           = 'rate';
    const DISCLAIMER     = 'disclaimer';

    /**
     * Get installment_id
     * @return string|null
     */
    public function getInstallmentId();

    /**
     * Set installment_id
     * @param string $installmentId
     * @return \Ceb\Installments\Installment\Api\Data\InstallmentInterface
     */
    public function setInstallmentId($installmentId);

    /**
     * Get title
     * @return string|null
     */
    public function getName();

    /**
     * Set title
     * @param string $value
     * @return \Ceb\Installments\Installment\Api\Data\InstallmentInterface
     */
    public function setName($value);

    /**
     * Get title
     * @return string|null
     */
    public function getInstallments();

    /**
     * Set title
     * @param string $value
     * @return \Ceb\Installments\Installment\Api\Data\InstallmentInterface
     */
    public function setInstallments($value);

    /**
     * Get title
     * @return string|null
     */
    public function getRate();

    /**
     * Set title
     * @param string $value
     * @return \Ceb\Installments\Installment\Api\Data\InstallmentInterface
     */
    public function setRate($value);

    /**
     * Get title
     * @return string|null
     */
    public function getDisclaimer();

    /**
     * Set title
     * @param string $value
     * @return \Ceb\Installments\Installment\Api\Data\InstallmentInterface
     */
    public function setDisclaimer($value);
}

