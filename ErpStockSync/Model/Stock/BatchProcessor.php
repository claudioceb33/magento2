<?php
declare(strict_types=1);

namespace Ceb\ErpStockSync\Model\Stock;

use Generator;
use InvalidArgumentException;
use Traversable;

class BatchProcessor
{
    /**
     * @template T
     *
     * @param iterable<T> $items
     * @return Generator<int, array<int, T>>
     */
    public function createBatches(
        iterable $items,
        int $batchSize
    ): Generator {
        if ($batchSize <= 0) {
            throw new InvalidArgumentException(
                'Batch size must be greater than zero.'
            );
        }

        $batch = [];

        foreach ($items as $item) {
            $batch[] = $item;

            if (count($batch) >= $batchSize) {
                yield $batch;

                $batch = [];
            }
        }

        if ($batch !== []) {
            yield $batch;
        }
    }
}