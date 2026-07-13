<?php
/**
 * Copyright © landofcoder.com All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Lof\Formbuilder\Model\Data;

use Lof\Formbuilder\Api\Data\FieldOptionInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class FieldOption extends AbstractSimpleObject implements FieldOptionInterface
{
    /**
     * @inheritdoc
     */
    public function getLabel(): ?string
    {
        return $this->_get(self::LABEL);
    }

    /**
     * @inheritdoc
     */
    public function setLabel(string $label): static
    {
        return $this->setData(self::LABEL, $label);
    }

    /**
     * @inheritdoc
     */
    public function getChecked(): ?bool
    {
        return $this->_get(self::CHECKED);
    }

    /**
     * @inheritdoc
     */
    public function setChecked(bool $checked): static
    {
        return $this->setData(self::CHECKED, $checked);
    }

}
