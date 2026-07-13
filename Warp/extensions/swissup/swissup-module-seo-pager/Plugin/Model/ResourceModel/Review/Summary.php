<?php

namespace Swissup\SeoPager\Plugin\Model\ResourceModel\Review;

use Magento\Review\Model\ResourceModel\Review\Summary as Subject;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

class Summary
{
    /**
     * Plugin helps to avoid exception related to Magento_Review.
     *
     * Exception:
     *  > You cannot define a correlation name 'review_summary' more than once
     */
    public function aroundAppendSummaryFieldsToCollection(
        Subject $subject,
        callable $proceed,
        Collection $productCollection,
        int $storeId,
        string $entityCode
    ) {
        $select = $productCollection->getSelect();
        $from = $select->getPart($select::FROM);
        if (isset($from['review_summary'])) {
            return $subject;
        }

        return $proceed(
            $productCollection,
            $storeId,
            $entityCode
        );
    }
}
