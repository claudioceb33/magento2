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

declare(strict_types=1);

namespace Lof\Formbuilder\Model\ResourceModel\Blacklist;

use Lof\Formbuilder\Model\Blacklist;
use Lof\Formbuilder\Model\ResourceModel\AbstractCollection;
use Magento\Store\Model\Store;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'blacklist_id';

    /**
     * Define resource model
     *
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(Blacklist::class, \Lof\Formbuilder\Model\ResourceModel\Blacklist::class);
    }

    /**
     * Add filter by store
     *
     * @param array|int|Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter(Store|array|int $store, bool $withAdmin = true): static
    {
        $this->performAddStoreFilter($store, $withAdmin);

        return $this;
    }

    /**
     * add emails to filer
     *
     * @param mixed|array $emails
     *
     * @return $this
     */
    public function addEmailsToFilter(mixed $emails): static
    {
        $this->addFieldToFilter('email', ['in' => $emails]);
        return $this;
    }
}
