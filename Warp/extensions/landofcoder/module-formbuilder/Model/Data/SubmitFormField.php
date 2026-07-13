<?php
/**
 * Copyright © landofcoder.com All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Lof\Formbuilder\Model\Data;

use Lof\Formbuilder\Api\Data\SubmitFormFieldInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class SubmitFormField extends AbstractSimpleObject implements SubmitFormFieldInterface
{
    /**
     * @inheritdoc
     */
    public function getCid(): ?string
    {
        return $this->_get(self::CID);
    }

    /**
     * @inheritdoc
     */
    public function setCid(string $cid): static
    {
        return $this->setData(self::CID, $cid);
    }

    /**
     * @inheritdoc
     */
    public function getFieldName(): ?string
    {
        return $this->_get(self::FIELD_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setFieldName(string $fieldName): static
    {
        return $this->setData(self::FIELD_NAME, $fieldName);
    }

    /**
     * @inheritdoc
     */
    public function getValue(): mixed
    {
        return $this->_get(self::FIELD_VALUE);
    }

    /**
     * @inheritdoc
     */
    public function setValue(mixed $value): static
    {
        return $this->setData(self::FIELD_VALUE, $value);
    }
}
