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

namespace Lof\Formbuilder\Api\Data;

interface FormbuilderMessageInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const MESSAGE_ID = 'message_id';
    public const FORM_ID = 'form_id';
    public const PRODUCT_ID = 'product_id';
    public const CUSTOMER_ID = 'customer_id';
    public const SUBJECT = 'subject';
    public const EMAIL_FROM = 'email_from';
    public const CREATION_TIME = 'creation_time';
    public const MESSAGE = 'message';
    public const IP_ADDRESS = 'ip_address';
    public const PARAMS = 'params';
    public const EMAIL_TO = 'email_to';
    public const QRCODE = 'qrcode';

    /**
     * Get Message_id
     *
     * @return int|null
     */
    public function getMessageId(): ?int;

    /**
     * Get email to
     *
     * @return string|null
     */
    public function getEmailTo(): ?string;

    /**
     * Get form_id
     *
     * @return int|null
     */
    public function getFormId(): ?int;

    /**
     * Get product_id
     *
     * @return int|null
     */
    public function getProductId(): ?int;

    /**
     * Get customer_id
     *
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * Get subject
     *
     * @return string|null
     */
    public function getSubject(): ?string;

    /**
     * Get email_from
     *
     * @return string
     *
     */
    public function getEmailFrom(): string;

    /**
     * Get creation_time
     *
     * @return string|null
     */
    public function getCreationTime(): ?string;

    /**
     * Get message
     *
     * @return string|null
     */
    public function getMessage(): ?string;

    /**
     * Get ip_address
     *
     * @return string|null
     */
    public function getIpAddress(): ?string;

    /**
     * Get params
     *
     * @return string|null
     */
    public function getParams(): ?string;

    /**
     * Set message_id
     *
     * @param int $messageId
     * @return $this
     */
    public function setMessageId(int $messageId): static;

    /**
     * Set email_to
     *
     * @param string $emailTo
     * @return $this
     */
    public function setEmailTo(string $emailTo): static;

    /**
     * Set form_id
     *
     * @param int $id
     * @return $this
     */
    public function setFormId(int $id): static;

    /**
     * Set product_id
     *
     * @param int $productId
     * @return $this
     */
    public function setProductId(int $productId): static;

    /**
     * Set customer_id
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId(int $customerId): static;

    /**
     * Set subject
     *
     * @param string $subject
     * @return $this
     */
    public function setSubject(string $subject): static;

    /**
     * Set email_from
     *
     * @param int $emailFrom
     * @return $this
     *
     */
    public function setEmailFrom(int $emailFrom): static;

    /**
     * Set creation_time
     *
     * @param string $creationTime
     * @return $this
     */
    public function setCreationTime(string $creationTime): static;

    /**
     * Set message
     *
     * @param string $message
     * @return $this
     */
    public function setMessage(string $message): static;

    /**
     * Set ip_address
     *
     * @param string $ipAddress
     * @return $this
     */
    public function setIpAddress(string $ipAddress): static;

    /**
     * Set params
     *
     * @param string $params
     * @return $this
     */
    public function setParams(string $params): static;

    /**
     * Get qrcode
     *
     * @return string|null
     */
    public function getQrcode(): ?string;

    /**
     * Set qrcode
     * @param string $qrcode
     * @return $this
     */
    public function setQrcode(string $qrcode): static;
}
