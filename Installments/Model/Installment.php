<?php
declare(strict_types=1);

namespace Ceb\Installments\Model;

use Ceb\Installments\Api\Data\InstallmentInterface;
use Magento\Framework\Model\AbstractModel;

class Installment extends AbstractModel implements InstallmentInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Ceb\Installments\Model\ResourceModel\Installment::class);
    }

    /**
     * @inheritDoc
     */
    public function getInstallmentId()
    {
        return $this->getData(self::INSTALLMENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setInstallmentId($installmentId)
    {
        return $this->setData(self::INSTALLMENT_ID, $installmentId);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName($value)
    {
        return $this->setData(self::NAME, $value);
    }

    /**
     * @inheritDoc
     */
    public function getInstallments()
    {
        return $this->getData(self::INSTALLMENTS);
    }

    /**
     * @inheritDoc
     */
    public function setInstallments($value)
    {
        return $this->setData(self::INSTALLMENTS, $value);
    }

    /**
     * @inheritDoc
     */
    public function getRate()
    {
        return $this->getData(self::RATE);
    }

    /**
     * @inheritDoc
     */
    public function setRate($value)
    {
        return $this->setData(self::RATE, $value);
    }

    /**
     * @inheritDoc
     */
    public function getDisclaimer()
    {
        return $this->getData(self::DISCLAIMER);
    }

    /**
     * @inheritDoc
     */
    public function setDisclaimer($value)
    {
        return $this->setData(self::DISCLAIMER, $value);
    }
}

