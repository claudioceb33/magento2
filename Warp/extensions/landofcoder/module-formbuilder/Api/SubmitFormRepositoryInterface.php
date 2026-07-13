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

namespace Lof\Formbuilder\Api;

use Lof\Formbuilder\Api\Data\SubmitFormInterface;
use Magento\Framework\Exception\LocalizedException;

interface SubmitFormRepositoryInterface
{
    /**
     * POST for form data api by ID
     *
     * @param int $customerId
     * @param SubmitFormInterface $formData
     * @param int|null $storeId
     * @return int
     * @throws LocalizedException
     */
    public function submitForm(
        int $customerId,
        SubmitFormInterface $formData,
        int $storeId = null
    ): int;

}
