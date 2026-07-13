<?php
/**
 * Copyright © landofcoder.com All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Lof\Formbuilder\Model\Data;

use Lof\Formbuilder\Api\Data\SubmitFormInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class SubmitForm extends AbstractSimpleObject implements SubmitFormInterface
{
    /**
     * @inheritdoc
     */
    public function getFormId(): int
    {
        return $this->_get(self::FORM_ID);
    }

    /**
     * @inheritdoc
     */
    public function setFormId(int $formId): static
    {
        return $this->setData(self::FORM_ID, $formId);
    }

    /**
     * @inheritdoc
     */
    public function getProductId(): int
    {
        return $this->_get(self::PRODUCT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setProductId(int $productId): static
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * @inheritdoc
     */
    public function getCaptcha(): string
    {
        return $this->_get(self::CAPTCHA);
    }

    /**
     * @inheritdoc
     */
    public function setCaptcha(string $captcha): static
    {
        return $this->setData(self::CAPTCHA, $captcha);
    }

    /**
     * @inheritdoc
     */
    public function getFields(): array
    {
        return $this->_get(self::FIELDS);
    }

    /**
     * @inheritdoc
     */
    public function setFields(array $fields): static
    {
        return $this->setData(self::FIELDS, $fields);
    }

}
