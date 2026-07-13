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

interface SubmitFormFieldInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const CID = 'cid';
    public const FIELD_NAME = 'field_name';
    public const FIELD_VALUE = 'value';

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
     * Get field_name
     *
     * @return string|null
     */
    public function getFieldName(): ?string;

    /**
     * Set field_name
     *
     * @param string $fieldName
     * @return $this
     */
    public function setFieldName(string $fieldName): static;

    /**
     * Get value
     *
     * @return mixed|string|null
     */
    public function getValue(): mixed;

    /**
     * Set value
     *
     * @param mixed|string $value
     * @return $this
     */
    public function setValue(mixed $value): static;
}
