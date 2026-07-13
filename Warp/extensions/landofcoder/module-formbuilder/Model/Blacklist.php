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

namespace Lof\Formbuilder\Model;

use Lof\Formbuilder\Model\ResourceModel\Blacklist\Collection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Registry;

class Blacklist extends AbstractModel
{
    /**#@+
     * Form's Statuses
     */
    public const STATUS_ENABLED = 1;
    public const STATUS_DISABLED = 0;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'formbuilder_blacklist';

    /**
     * Name of the event object
     *
     * @var string
     */
    protected $_eventObject = 'blacklist';

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ResourceModel\Blacklist|null $resource
     * @param Collection|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ResourceModel\Blacklist $resource = null,
        Collection $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Blacklist::class);
    }

    /**
     * @param int $messageId
     * @return AbstractDb|AbstractCollection|null
     */
    public function loadListByMessageId(int $messageId = 0): AbstractDb|AbstractCollection|null
    {
        return $this->getCollection()
            ->addFieldToFilter('message_id', (int)$messageId);
    }

    /**
     * @param int $formId
     * @return AbstractDb|AbstractCollection|null
     */
    public function loadListByFormId(int $formId = 0): AbstractDb|AbstractCollection|null
    {
        return $this->getCollection()
            ->addFieldToFilter('form_id', (int)$formId);
    }

    /**
     * @param $emailAddress
     * @return AbstractResource|\Magento\Framework\Model\ResourceModel\Db\AbstractDb|null
     */
    public function loadByEmail(
        $emailAddress
    ): \Magento\Framework\Model\ResourceModel\Db\AbstractDb|AbstractResource|null {
        return $this->getResource()->load($this, $emailAddress, 'email');
    }

    /**
     * @param $ipAddress
     * @return AbstractResource|\Magento\Framework\Model\ResourceModel\Db\AbstractDb|null
     */
    public function loadByIp($ipAddress): \Magento\Framework\Model\ResourceModel\Db\AbstractDb|AbstractResource|null
    {
        return $this->getResource()->load($this, $ipAddress, 'ip');
    }

    /**
     * @return array
     */
    public function getAvailableStatuses(): array
    {
        return [self::STATUS_ENABLED => __('Blocked'), self::STATUS_DISABLED => __('Un Blocked')];
    }
}
