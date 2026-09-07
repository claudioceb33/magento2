<?php
declare(strict_types=1);

namespace Ceb\ErpStockSync\Api;

interface ProductSkuValidatorInterface
{
    /**
     * @param string[] $skus
     * @return string[]
     */
    public function getExistingSkus(array $skus): array;
}