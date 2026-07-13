<?php
/**
 * Copyright © landofcoder.com All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Lof\Formbuilder\Model\Data;

use Lof\Formbuilder\Api\Data\FieldDesignInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class FieldDesign extends AbstractSimpleObject implements FieldDesignInterface
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
    public function getFieldType(): ?string
    {
        return $this->_get(self::FIELD_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setFieldType(string $fieldType): static
    {
        return $this->setData(self::FIELD_TYPE, $fieldType);
    }

    /**
     * @inheritdoc
     */
    public function getRequired(): ?string
    {
        return $this->_get(self::REQUIRED);
    }

    /**
     * @inheritdoc
     */
    public function setRequired(bool $required): static
    {
        return $this->setData(self::REQUIRED, $required);
    }

    /**
     * @inheritdoc
     */
    public function getFieldOptions(): ?string
    {
        return $this->_get(self::FIELD_OPTIONS);
    }

    /**
     * @inheritdoc
     */
    public function setFieldOptions(mixed $fieldOptions): static
    {
        return $this->setData(self::FIELD_OPTIONS, $fieldOptions);
    }

    /**
     * @inheritdoc
     */
    public function getFieldcol(): ?string
    {
        return $this->_get(self::FIELD_COL);
    }

    /**
     * @inheritdoc
     */
    public function setFieldcol(int|string $fieldCol): static
    {
        return $this->setData(self::FIELD_COL, $fieldCol);
    }

    /**
     * @inheritdoc
     */
    public function getWrappercol(): ?string
    {
        return $this->_get(self::WRAPPERCOL);
    }

    /**
     * @inheritdoc
     */
    public function setWrappercol(int|string $wrappercol): static
    {
        return $this->setData(self::WRAPPERCOL, $wrappercol);
    }

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
    public function getFieldId(): ?string
    {
        return $this->_get(self::FIELD_ID);
    }

    /**
     * @inheritdoc
     */
    public function setFieldId(string $fieldId): static
    {
        return $this->setData(self::FIELD_ID, $fieldId);
    }

    /**
     * @inheritdoc
     */
    public function getInlineCss(): ?string
    {
        return $this->_get(self::INLINE_CSS);
    }

    /**
     * @inheritdoc
     */
    public function setInlineCss(?string $inlineCss): static
    {
        return $this->setData(self::INLINE_CSS, $inlineCss);
    }

    /**
     * @inheritdoc
     */
    public function getFieldSize(): ?string
    {
        return $this->_get(self::FIELD_SIZE);
    }

    /**
     * @inheritdoc
     */
    public function setFieldSize(?string $fieldSize): static
    {
        return $this->setData(self::FIELD_SIZE, $fieldSize);
    }

    /**
     * @inheritdoc
     */
    public function getFontWeight(): ?string
    {
        return $this->_get(self::FONT_WEIGHT);
    }

    /**
     * @inheritdoc
     */
    public function setFontWeight(?string $fontWeight): static
    {
        return $this->setData(self::FONT_WEIGHT, $fontWeight);
    }

    /**
     * @inheritdoc
     */
    public function getColorText(): ?string
    {
        return $this->_get(self::COLOR_TEXT);
    }

    /**
     * @inheritdoc
     */
    public function setColorText(?string $colorText): static
    {
        return $this->setData(self::COLOR_TEXT, $colorText);
    }

    /**
     * @inheritdoc
     */
    public function getFontSize(): ?string
    {
        return $this->_get(self::FONT_SIZE);
    }

    /**
     * @inheritdoc
     */
    public function setFontSize(?string $fontSize): static
    {
        return $this->setData(self::FONT_SIZE, $fontSize);
    }

    /**
     * @inheritdoc
     */
    public function getColorLabel(): ?string
    {
        return $this->_get(self::COLOR_LABEL);
    }

    /**
     * @inheritdoc
     */
    public function setColorLabel(?string $colorLabel): static
    {
        return $this->setData(self::COLOR_LABEL, $colorLabel);
    }

    /**
     * @inheritdoc
     */
    public function getValidation(): ?string
    {
        return $this->_get(self::VALIDATION);
    }

    /**
     * @inheritdoc
     */
    public function setValidation(?string $validation): static
    {
        return $this->setData(self::VALIDATION, $validation);
    }

    /**
     * @inheritdoc
     */
    public function getIncludeBlankOption(): ?string
    {
        return $this->_get(self::INCLUDE_BLANK_OPTION);
    }

    /**
     * @inheritdoc
     */
    public function setIncludeBlankOption(?string $includeBlankOption): static
    {
        return $this->setData(self::INCLUDE_BLANK_OPTION, $includeBlankOption);
    }

    /**
     * @inheritdoc
     */
    public function getOptions(): ?string
    {
        return $this->_get(self::OPTIONS);
    }

    /**
     * @inheritdoc
     */
    public function setOptions(mixed $options): static
    {
        return $this->setData(self::OPTIONS, $options);
    }

}
