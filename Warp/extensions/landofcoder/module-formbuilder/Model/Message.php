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

use Lof\Formbuilder\Api\Data\FormbuilderMessageInterface;
use Lof\Formbuilder\Helper\Data;
use Lof\Formbuilder\Model\ResourceModel\Message\Collection;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

class Message extends AbstractModel implements FormbuilderMessageInterface
{
    public const CACHE_TAG = 'formbuilder_message';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'formbuilder_message';

    /**
     * Name of the event object
     *
     * @var string
     */
    protected $_eventObject = 'message';

    /**
     * @var Data
     */
    protected Data $formHelper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ResourceModel\Message|null $resource
     * @param ResourceModel\Message\Collection|null $resourceCollection
     * @param Data $formHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $formHelper,
        ResourceModel\Message $resource = null,
        Collection $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->formHelper = $formHelper;
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Message::class);
    }

    /**
     * get safed message
     *
     * @param string $message
     * @param bool $stripTags
     * @return string
     */
    public function getSafedMessage(string $message = "", bool $stripTags = false): string
    {
        if ($message) {
            $message = $this->formHelper->xssClean($message);
            if ($stripTags) {
                $message = strip_tags($message);
            }
        } else {
            $message = "";
            if ($this->getId() && $this->getMessage()) {
                $message = $this->getMessage();
                $message = $this->formHelper->xssClean($message);
                if ($stripTags) {
                    $message = strip_tags($message);
                }
            }
        }

        return $message;
    }

    /**
     * @inheritdoc
     */
    public function getMessageId(): ?int
    {
        return $this->getData(self::MESSAGE_ID);
    }

    /**
     * @inheritdoc
     */
    public function getEmailTo(): ?string
    {
        return $this->getData(self::EMAIL_TO);
    }

    /**
     * @inheritdoc
     */
    public function getFormId(): ?int
    {
        return $this->getData(self::FORM_ID);
    }

    /**
     * @inheritdoc
     */
    public function getProductId(): ?int
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId(): int
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function getSubject(): ?string
    {
        return $this->getData(self::SUBJECT);
    }

    /**
     * @inheritdoc
     *
     */
    public function getEmailFrom(): string
    {
        return $this->getData(self::EMAIL_FROM);
    }

    /**
     * @inheritdoc
     */
    public function getCreationTime(): ?string
    {
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * @inheritdoc
     */
    public function getMessage(): ?string
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * @inheritdoc
     */
    public function getIpAddress(): ?string
    {
        return $this->getData(self::IP_ADDRESS);
    }

    /**
     * @inheritdoc
     */
    public function getParams(): ?string
    {
        return $this->getData(self::PARAMS);
    }

    /**
     * @inheritdoc
     */
    public function setMessageId(int $messageId): static
    {
        return $this->setData(self::MESSAGE_ID, $messageId);
    }

    /**
     * @inheritdoc
     */
    public function setEmailTo(string $emailTo): static
    {
        return $this->setData(self::EMAIL_TO, $emailTo);
    }

    /**
     * @inheritdoc
     */
    public function setFormId(int $id): static
    {
        return $this->setData(self::FORM_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function setProductId(int $productId): static
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId(int $customerId): static
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritdoc
     */
    public function setSubject(string $subject): static
    {
        return $this->setData(self::EMAIL_FROM, $subject);
    }

    /**
     * @inheritdoc
     *
     */
    public function setEmailFrom(int $emailFrom): static
    {
        return $this->setData(self::EMAIL_FROM, $emailFrom);
    }

    /**
     * @inheritdoc
     */
    public function setCreationTime(string $creationTime): static
    {
        return $this->setData(self::CREATION_TIME, $creationTime);
    }

    /**
     * @inheritdoc
     */
    public function setMessage(string $message): static
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * @inheritdoc
     */
    public function setIpAddress(string $ipAddress): static
    {
        return $this->setData(self::IP_ADDRESS, $ipAddress);
    }

    /**
     * @inheritdoc
     */
    public function setParams(string $params): static
    {
        return $this->setData(self::PARAMS, $params);
    }

    /**
     * @inheritdoc
     */
    public function getQrcode(): ?string
    {
        return $this->getData(self::QRCODE);
    }

    /**
     * @inheritdoc
     */
    public function setQrcode(string $qrcode): static
    {
        return $this->setData(self::QRCODE, $qrcode);
    }
}
