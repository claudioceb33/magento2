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

interface FieldOptionInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const LABEL = 'label';
    public const CHECKED = 'checked';

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
     * Get checked
     *
     * @return bool|null
     */
    public function getChecked(): ?bool;

    /**
     * Set label
     *
     * @param bool $checked
     * @return $this
     */
    public function setChecked(bool $checked): static;
}
