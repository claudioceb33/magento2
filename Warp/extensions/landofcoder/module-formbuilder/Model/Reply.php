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

use Lof\Formbuilder\Model\ResourceModel\Reply\Collection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Registry;

class Reply extends AbstractModel
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
    protected $_eventPrefix = 'formbuilder_reply';

    /**
     * Name of the event object
     *
     * @var string
     */
    protected $_eventObject = 'reply';

    /**
     * Reply constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ResourceModel\Reply|null $resource
     * @param ResourceModel\Reply\Collection|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ResourceModel\Reply $resource = null,
        Collection $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init(ResourceModel\Reply::class);
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
     * @param $emailToAddress
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb|AbstractResource|null
     */
    public function loadByEmailTo(
        $emailToAddress
    ): \Magento\Framework\Model\ResourceModel\Db\AbstractDb|AbstractResource|null {
        return $this->getResource()->load($this, $emailToAddress, 'email_to');
    }

    /**
     * @param $emailFromAddress
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb|AbstractResource|null
     */
    public function loadByEmailFrom(
        $emailFromAddress
    ): \Magento\Framework\Model\ResourceModel\Db\AbstractDb|AbstractResource|null {
        return $this->getResource()->load($this, $emailFromAddress, 'email_from');
    }

    /**
     * @return array
     */
    public function getAvailableStatuses(): array
    {
        return [self::STATUS_ENABLED => __('Sent'), self::STATUS_DISABLED => __('Un Sent')];
    }
}
