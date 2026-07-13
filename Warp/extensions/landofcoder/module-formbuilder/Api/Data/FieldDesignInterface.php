<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_Formbuilder
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\Formbuilder\Api\Data;

interface FieldDesignInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const LABEL = 'label';
    public const FIELD_ID = 'field_id';
    public const FIELD_TYPE = 'field_type';
    public const REQUIRED = 'required';
    public const FIELD_OPTIONS = 'field_options';
    public const FIELD_COL = 'fieldcol';
    public const WRAPPERCOL = 'wrappercol';
    public const CID = 'cid';
    public const INLINE_CSS = 'inline_css';
    public const FIELD_SIZE = 'field_size';
    public const FONT_WEIGHT = 'font_weight';
    public const COLOR_TEXT = 'color_text';
    public const FONT_SIZE = 'font_size';
    public const COLOR_LABEL = 'color_label';
    public const VALIDATION = 'validation';
    public const INCLUDE_BLANK_OPTION = 'include_blank_option';
    public const OPTIONS = 'options';

    /**
     * Get label
     *
     * @return string|null
     */
    public function getLabel(): ?string;

    /**
     * Set label
     *
     * @param string $label
     * @return $this
     */
    public function setLabel(string $label): static;

    /**
     * Get field_type
     *
     * @return string|null
     */
    public function getFieldType(): ?string;

    /**
     * Set field_type
     *
     * @param string $fieldType
     * @return $this
     */
    public function setFieldType(string $fieldType): static;

    /**
     * Get required
     *
     * @return string|null
     */
    public function getRequired(): ?string;

    /**
     * Set required
     *
     * @param bool $required
     * @return $this
     */
    public function setRequired(bool $required): static;

    /**
     * Get field_options
     *
     * @return mixed|string
     */
    public function getFieldOptions(): mixed;

    /**
     * Set field_options
     *
     * @param mixed|string $fieldOptions
     * @return $this
     */
    public function setFieldOptions(mixed $fieldOptions): static;

    /**
     * Get fieldcol
     *
     * @return string|null
     */
    public function getFieldcol(): ?string;

    /**
     * Set fieldcol
     *
     * @param int|string $fieldCol
     * @return $this
     */
    public function setFieldcol(int|string $fieldCol): static;

    /**
     * Get wrappercol
     *
     * @return string
     */
    public function getWrappercol(): ?string;

    /**
     * Set wrappercol
     *
     * @param int|string $wrappercol
     * @return $this
     */
    public function setWrappercol(int|string $wrappercol): static;

    /**
     * Get cid
     *
     * @return string|null
     */
    public function getCid(): ?string;

    /**
     * Set cid
     *
     * @param string $cid
     * @return $this
     */
    public function setCid(string $cid): static;

    /**
     * Get field_id
     *
     * @return string|null
     */
    public function getFieldId(): ?string;

    /**
     * Set field_id
     *
     * @param string $fieldId
     * @return $this
     */
    public function setFieldId(string $fieldId): static;

    /**
     * Get inline_css
     *
     * @return string|null
     */
    public function getInlineCss(): ?string;

    /**
     * Set inline_css
     *
     * @param string|null $inlineCss
     * @return $this
     */
    public function setInlineCss(?string $inlineCss): static;

    /**
     * Get field_size
     *
     * @return string|null
     */
    public function getFieldSize(): ?string;

    /**
     * Set field_size
     *
     * @param string|null $fieldSize
     * @return $this
     */
    public function setFieldSize(?string $fieldSize): static;

    /**
     * Get font_weight
     *
     * @return string|null
     */
    public function getFontWeight(): ?string;

    /**
     * Set font_weight
     *
     * @param string|null $fontWeight
     * @return $this
     */
    public function setFontWeight(?string $fontWeight): static;

    /**
     * Get color_text
     *
     * @return string|null
     */
    public function getColorText(): ?string;

    /**
     * Set color_text
     *
     * @param string|null $colorText
     * @return $this
     */
    public function setColorText(?string $colorText): static;

    /**
     * Get font_size
     *
     * @return string|null
     */
    public function getFontSize(): ?string;

    /**
     * Set font_size
     *
     * @param string|null $fontSize
     * @return $this
     */
    public function setFontSize(?string $fontSize): static;

    /**
     * Get color_label
     *
     * @return string|null
     */
    public function getColorLabel(): ?string;

    /**
     * Set color_label
     *
     * @param string|null $colorLabel
     * @return $this
     */
    public function setColorLabel(?string $colorLabel): static;

    /**
     * Get validation
     *
     * @return string|null
     */
    public function getValidation(): ?string;

    /**
     * Set validation
     *
     * @param string|null $validation
     * @return $this
     */
    public function setValidation(?string $validation): static;

    /**
     * Get include_blank_option
     *
     * @return string|null
     */
    public function getIncludeBlankOption(): ?string;

    /**
     * Set include_blank_option
     *
     * @param string|null $includeBlankOption
     * @return $this
     */
    public function setIncludeBlankOption(?string $includeBlankOption): static;

    /**
     * Get options
     *
     * @return FieldOptionInterface[]|mixed|string|null
     */
    public function getOptions(): mixed;

    /**
     * Set options
     *
     * @param FieldOptionInterface[]|mixed|string|null $options
     * @return $this
     */
    public function setOptions(mixed $options): static;
}
