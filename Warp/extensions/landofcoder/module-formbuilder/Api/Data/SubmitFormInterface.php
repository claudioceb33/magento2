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

interface SubmitFormInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const FIELDS = 'fields';
    public const FORM_ID = 'form_id';
    public const PRODUCT_ID = 'product_id';
    public const CAPTCHA = 'captcha';

    /**
     * Get form_id
     *
     * @return int
     */
    public function getFormId(): int;

    /**
     * Set form_id
     *
     * @param int $formId
     * @return $this
     */
    public function setFormId(int $formId): static;

    /**
     * Get product_id
     *
     * @return int
     */
    public function getProductId(): int;

    /**
     * Set product_id
     *
     * @param int $productId
     * @return $this
     */
    public function setProductId(int $productId): static;

    /**
     * Get captcha
     *
     * @return string
     */
    public function getCaptcha(): string;

    /**
     * Set captcha
     *
     * @param string $captcha
     * @return $this
     */
    public function setCaptcha(string $captcha): static;

    /**
     * Get fields
     *
     * @return SubmitFormFieldInterface[]
     */
    public function getFields(): array;

    /**
     * Set fields
     *
     * @param SubmitFormFieldInterface[] $fields
     * @return $this
     */
    public function setFields(array $fields): static;
}
