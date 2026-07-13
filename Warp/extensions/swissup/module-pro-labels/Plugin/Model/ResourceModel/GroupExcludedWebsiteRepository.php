<?php

namespace Swissup\ProLabels\Plugin\Model\ResourceModel;

use Magento\Customer\Model\ResourceModel\GroupExcludedWebsiteRepository as Subject;

class GroupExcludedWebsiteRepository
{
    /**
     * @var array
     */
    private $memo;

    /**
     * Memoization to prevent multiple DB queries.
     * Issue occurs when tax is set up for
     * method `Helper\Data::getConfigurableProductDiscountValue`.
     *
     * @param  Subject  $subject
     * @param  callable $proceed
     * @param  int      $customerGroupId
     * @return array
     */
    public function aroundGetCustomerGroupExcludedWebsites(
        Subject $subject,
        callable $proceed,
        $customerGroupId
    ) {
        if (!isset($this->memo[$customerGroupId])) {
            $this->memo[$customerGroupId] = $proceed($customerGroupId);
        }

        return $this->memo[$customerGroupId];
    }
}
