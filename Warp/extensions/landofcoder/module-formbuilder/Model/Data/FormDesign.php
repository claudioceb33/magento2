<?php

/**
 * Copyright © landofcoder.com All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Lof\Formbuilder\Model\Data;

use Lof\Formbuilder\Api\Data\FormbuilderDesignInterface;
use Lof\Formbuilder\Api\Data\FormbuilderInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class FormDesign extends AbstractSimpleObject implements FormbuilderDesignInterface
{
    /**
     * @inheritdoc
     */
    public function getForm(): FormbuilderInterface
    {
        return $this->_get(self::FORM);
    }

    /**
     * @inheritdoc
     */
    public function setForm(FormbuilderInterface $form): static
    {
        return $this->setData(self::FORM, $form);
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
