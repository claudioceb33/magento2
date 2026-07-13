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

namespace Lof\Formbuilder\Api;

use Lof\Formbuilder\Api\Data\FormbuilderDesignInterface;
use Lof\Formbuilder\Api\Data\FormbuilderInterface;
use Lof\Formbuilder\Api\Data\FormbuilderMessageInterface;
use Lof\Formbuilder\Api\Data\FormbuilderMessageSearchResultsInterface;
use Lof\Formbuilder\Api\Data\FormbuilderSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface FormbuilderRepositoryInterface
{
    /**
     * Retrieve Formbuilder matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @param int|null $customerGroupId
     * @param int|null $store
     * @return FormbuilderSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria,
        int $customerGroupId = null,
        int $store = null
    ): FormbuilderSearchResultsInterface;

    /**
     *  GET for form api by ID
     * @param int $formId
     * @return FormbuilderInterface
     * @throws LocalizedException
     */
    public function getFormById(int $formId): FormbuilderInterface;

    /**
     *  GET for form api by ID
     * @param int $formId
     * @param int|null $customerGroupId
     * @param int|null $store
     * @return FormbuilderInterface
     * @throws LocalizedException
     */
    public function getAvailableFormById(
        int $formId,
        int $customerGroupId = null,
        int $store = null
    ): FormbuilderInterface;

    /**
     * Save Form.
     * @param FormbuilderInterface $form
     * @return FormbuilderInterface
     * @throws LocalizedException
     */
    public function save(FormbuilderInterface $form): FormbuilderInterface;

    /**
     * Delete Form by ID
     * @param int $formId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(int $formId): bool;

    /**
     * Retrieve Formbuilder matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return FormbuilderMessageSearchResultsInterface
     * @throws LocalizedException
     */
    public function getListMessage(SearchCriteriaInterface $searchCriteria): FormbuilderMessageSearchResultsInterface;

    /**
     * Retrieve Formbuilder matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return FormbuilderMessageSearchResultsInterface
     * @throws LocalizedException
     */
    public function getLastMessage(SearchCriteriaInterface $searchCriteria): FormbuilderMessageSearchResultsInterface;

    /**
     *  GET for message api by ID
     * @param int $messageId
     * @return FormbuilderMessageInterface
     * @throws LocalizedException
     */
    public function getMessageById(int $messageId): FormbuilderMessageInterface;

    /**
     * Delete Message by ID
     * @param int $messageId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteMessageById(int $messageId): bool;

    /**
     * get Message content data from Form field
     * @param int $customerId
     * @param int $messageId
     * @return mixed
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getFormFieldData(int $customerId, int $messageId): mixed;

    /**
     * GET message of customer
     * @param int $customerId
     * @param int $limit = 10
     * @param int $page = 1
     * @return mixed
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getMessageContent(int $customerId, int $limit = 10, int $page = 1): mixed;

    /**
     * Retrieve Formbuilder matching the specified criteria.
     *
     * @param int $customerId
     * @param SearchCriteriaInterface $searchCriteria
     * @return FormbuilderMessageSearchResultsInterface
     * @throws LocalizedException
     */
    public function getMyListMessage(
        int $customerId,
        SearchCriteriaInterface $searchCriteria
    ): FormbuilderMessageSearchResultsInterface;

    /**
     * GET for my message api by ID
     *
     * @param int $customerId
     * @param int $messageId
     * @return FormbuilderMessageInterface
     * @throws LocalizedException
     */
    public function getMyMessageById(int $customerId, int $messageId): FormbuilderMessageInterface;

    /**
     * GET Form form field by ID, identifier, customer_group_id, stores_id
     * @param int $formId
     * @param int|null $customerGroupId
     * @param int|null $storeId
     * @return mixed
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getFormField(int $formId, int $customerGroupId = null, int $storeId = null): mixed;

    /**
     * GET Form form data with design fields by ID, identifier, customer_group_id, stores_id
     * @param int $formId
     * @param int|null $customerGroupId
     * @param int|null $storeId
     * @return FormbuilderDesignInterface
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getFormDesign(
        int $formId,
        int $customerGroupId = null,
        int $storeId = null
    ): FormbuilderDesignInterface;

    /**
     * POST for send reply message api
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function send(): bool;

    /**
     * POST add IP to blacklist
     * @return bool true in success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function addIp(): bool;
}
