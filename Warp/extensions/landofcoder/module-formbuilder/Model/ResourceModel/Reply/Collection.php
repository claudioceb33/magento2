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

namespace Lof\Formbuilder\Model\ResourceModel\Reply;

use Lof\Formbuilder\Model\ResourceModel\AbstractCollection;
use Lof\Formbuilder\Model\ResourceModel\Reply;
use Magento\Store\Model\Store;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'reply_id';

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad(): static
    {
        return parent::_afterLoad();
    }

    /**
     * Define resource model
     *
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(\Lof\Formbuilder\Model\Reply::class, Reply::class);
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

    public function addEmailsToFilter($emails): static
    {
        $this->addFieldToFilter('email', ['in' => $emails]);
        return $this;
    }
}
