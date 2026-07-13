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

interface FormbuilderInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const FORM_ID = 'form_id';
    public const TITLE = 'title';
    public const IDENTIFIER = 'identifier';
    public const EMAIL_RECEIVE = 'email_receive';
    public const THANKS_EMAIL_TEMPLATE = 'thanks_email_template';
    public const EMAIL_TEMPLATE = 'email_template';
    public const SHOW_CAPTCHA = 'show_captcha';
    public const SHOW_TOP_LINKS = 'show_toplink';
    public const SUBMIT_BUTTON_TEXT = 'submit_button_text';
    public const SUCCESS_MESSAGE = 'success_message';
    public const CREATION_TIME = 'creation_time';
    public const UPDATE_TIME = 'update_time';
    public const BEFORE_FORM_CONTENT = 'before_form_content';
    public const AFTER_FORM_CONTENT = 'after_form_content';
    public const STATUS = 'status';
    public const DESIGN = 'design';
    public const PAGE_TITLE = 'page_title';
    public const REDIRECT_LINK = 'redirect_link';
    public const PAGE_LAYOUT = 'page_layout';
    public const LAYOUT_UPDATE_XML = 'layout_update_xml';
    public const META_KEYWORDS = 'meta_keywords';
    public const META_DESCRIPTION = 'meta_description';
    public const THANKYOU_FIELD = 'thankyou_field';
    public const THANKYOU_EMAIL_TEMPLATE = 'thankyou_email_template';
    public const SUBMIT_TEXT_COLOR = 'submit_text_color';
    public const SUBMIT_BACKGROUND_COLOR = 'submit_background_color';
    public const SUBMIT_HOVER_COLOR = 'submit_hover_color';
    public const INPUT_HOVER_COLOR = 'input_hover_color ';
    public const CUSTOM_TEMPLATE = 'custom_template';
    public const SENDER_EMAIL_FIELD = 'sender_email_field';
    public const SENDER_NAME_FIELD = 'sender_name_field';
    public const TAGS = 'tags';
    public const ENABLE_TRACKLINK = 'enable_tracklink';
    public const CUSTOMER_GROUPS = 'customergroups';
    public const DESIGN_FIELDS = 'design_fields';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getFormId(): ?int;

    /**
     * Get form title
     *
     * @return string|null
     */
    public function getTitle(): ?string;

    /**
     * Get status
     *
     * @return int|null
     */
    public function getStatus(): ?int;

    /**
     * Get identifier
     *
     * @return string|null
     */
    public function getIdentifier(): ?string;

    /**
     * Get email_receive
     *
     * @return string|null
     */
    public function getEmailReceive(): ?string;

    /**
     * Get thanks_email_template
     *
     * @return string|null
     */
    public function getThanksEmailTemplate(): ?string;

    /**
     * Get email_template
     *
     * @return string|null
     */
    public function getEmailTemplate(): ?string;

    /**
     * Get show_captcha
     *
     * @return int|null
     */
    public function getShowCaptcha(): ?int;

    /**
     * Get show_toplink
     *
     * @return int|null
     */
    public function getShowToplink(): ?int;

    /**
     * Get submit_button_text
     *
     * @return string|null
     */
    public function getSubmitButtonText(): ?string;

    /**
     * Get success_message
     *
     * @return string|null
     */
    public function getSuccessMessage(): ?string;

    /**
     * Get creation_time
     *
     * @return string|null
     */
    public function getCreationTime(): ?string;

    /**
     * Get update_time
     *
     * @return string|null
     */
    public function getUpdateTime(): ?string;

    /**
     * Get before_form_content
     *
     * @return string|null
     */
    public function getBeforeFormContent(): ?string;

    /**
     * Get after_form_content
     *
     * @return string|null
     */
    public function getAfterFormContent(): ?string;

    /**
     * Get design
     *
     * @return string|null
     */
    public function getDesign(): ?string;

    /**
     * Get page_title
     *
     * @return string|null
     */
    public function getPageTitle(): ?string;

    /**
     * Get redirect_link
     *
     * @return string|null
     */
    public function getRedirectLink(): ?string;

    /**
     * Get page_layout
     *
     * @return string|null
     */
    public function getPageLayout(): ?string;

    /**
     * Get layout_update_xml
     *
     * @return string|null
     */
    public function geLayoutUpdateXml(): ?string;

    /**
     * Get meta_keywords
     *
     * @return string|null
     */
    public function getMetaKeywords(): ?string;

    /**
     * Get meta_description
     *
     * @return string|null
     */
    public function getMetaDescription(): ?string;

    /**
     * Get thankyou_field
     *
     * @return string|null
     */
    public function getThankyouField(): ?string;

    /**
     * Get thankyou_email_template
     *
     * @return string|null
     */
    public function getThankyouEmailTemplate(): ?string;

    /**
     * Get submit_text_color
     *
     * @return string|null
     */
    public function getSubmitTextColor(): ?string;

    /**
     * Get submit_background_color
     *
     * @return string|null
     */
    public function getSubmitBackgroundColor(): ?string;

    /**
     * Get submit_hover_color
     *
     * @return string|null
     */
    public function getSubmitHoverColor(): ?string;

    /**
     * Get input_hover_color
     *
     * @return string|null
     */
    public function getInputHoverColor(): ?string;

    /**
     * Get custom_template
     *
     * @return string|null
     */
    public function getCustomTemplate(): ?string;

    /**
     * Get sender_email_field
     *
     * @return string|null
     */
    public function getSenderEmailField(): ?string;

    /**
     * Get sender_name_field
     *
     * @return string|null
     */
    public function getSenderNameField(): ?string;

    /**
     * Set form_id
     *
     * @param int $id
     * @return FormbuilderInterface
     */
    public function setFormId(int $id): FormbuilderInterface;

    /**
     * Get tags
     *
     * @return string|null
     */
    public function getTags(): ?string;

    /**
     * Set tags
     *
     * @param string $tags
     * @return $this
     */
    public function setTags(string $tags): static;

    /**
     * Set title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): static;

    /**
     * Set status
     *
     * @param int $status
     * @return $this
     */
    public function setStatus(int $status): static;

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return $this
     */
    public function setIdentifier(string $identifier): static;

    /**
     * Set email_receive
     *
     * @param int $emailReceive
     * @return $this
     *
     */
    public function setEmailReceive(int $emailReceive): static;

    /**
     * Set thanks_email_template
     *
     * @param string $thanksEmailTemplate
     * @return $this
     */
    public function setThanksEmailTemplate(string $thanksEmailTemplate): static;

    /**
     * Set email_template
     *
     * @param string $emailTemplate
     * @return $this
     */
    public function setEmailTemplate(string $emailTemplate): static;

    /**
     * Set show_captcha
     *
     * @param int $showCaptcha
     * @return $this
     */
    public function setShowCaptcha(int $showCaptcha): static;

    /**
     * Set show_toplink
     *
     * @param int $showToplink
     * @return $this
     */
    public function setShowToplink(int $showToplink): static;

    /**
     * Set submit_button_text
     *
     * @param string $submitButtonText
     * @return $this
     */
    public function setSubmitButtonText(string $submitButtonText): static;

    /**
     * Set success_message
     *
     * @param string $successMessage
     * @return $this
     */
    public function setSuccessMessage(string $successMessage): static;

    /**
     * Set creation_time
     *
     * @param string $creationTime
     * @return $this
     */
    public function setCreationTime(string $creationTime): static;

    /**
     * Set update_time
     *
     * @param string $updateTime
     * @return $this
     */
    public function setUpdateTime(string $updateTime): static;

    /**
     * Set before_form_content
     *
     * @param string $beforeFormContent
     * @return $this
     */
    public function setBeforeFormContent(string $beforeFormContent): static;

    /**
     * Set after_form_content
     *
     * @param string $afterFormContent
     * @return $this
     */
    public function setAfterFormContent(string $afterFormContent): static;

    /**
     * Set design
     *
     * @param string $design
     * @return $this
     */
    public function setDesign(string $design): static;

    /**
     * Set page_title
     *
     * @param string $pageTitle
     * @return $this
     */
    public function setPageTitle(string $pageTitle): static;

    /**
     * Set redirect_link
     *
     * @param string $redirectLink
     * @return $this
     */
    public function setRedirectLink(string $redirectLink): static;

    /**
     * Set page_layout
     *
     * @param string $pageLayout
     * @return $this
     */
    public function setPageLayout(string $pageLayout): static;

    /**
     * Set layout_update_xml
     *
     * @param string $layoutUpdateXml
     * @return $this
     */
    public function setLayoutUpdateXml(string $layoutUpdateXml): static;

    /**
     * Set meta_keywords
     *
     * @param string $metaKeywords
     * @return $this
     */
    public function setMetaKeywords(string $metaKeywords): static;

    /**
     * Set meta_description
     *
     * @param string $metaDescription
     * @return $this
     */
    public function setMetaDescription(string $metaDescription): static;

    /**
     * Set thankyou_field
     *
     * @param string $thankyouField
     * @return $this
     */
    public function setThankyouField(string $thankyouField): static;

    /**
     * Set thankyou_email_template
     *
     * @param string $thankyouEmailTemplate
     * @return $this
     */
    public function setThankyouEmailTemplate(string $thankyouEmailTemplate): static;

    /**
     * Set submit_text_color
     *
     * @param string $submitTextColor
     * @return $this
     */
    public function setSubmitTextColor(string $submitTextColor): static;

    /**
     * Set submit_background_color
     *
     * @param string $submitBackgroundColor
     * @return $this
     */
    public function setSubmitBackgroundColor(string $submitBackgroundColor): static;

    /**
     * Set submit_hover_color
     *
     * @param string $submitHoverColor
     * @return $this
     */
    public function setSubmitHoverColor(string $submitHoverColor): static;

    /**
     * Set input_hover_color
     *
     * @param string $inputHoverColor
     * @return $this
     */
    public function setInputHoverColor(string $inputHoverColor): static;

    /**
     * Set custom_template
     *
     * @param string $customTemplate
     * @return $this
     */
    public function setCustomTemplate(string $customTemplate): static;

    /**
     * Set sender_email_field
     *
     * @param string $senderEmailField
     * @return $this
     */
    public function setSenderEmailField(string $senderEmailField): static;

    /**
     * Set sender_name_field
     *
     * @param string $senderNameField
     * @return $this
     */
    public function setSenderNameField(string $senderNameField): static;

    /**
     * Get enable_tracklink
     *
     * @return int|null
     */
    public function getEnableTracklink(): ?int;

    /**
     * Set enable_tracklink
     *
     * @param int $enableTracklink
     * @return $this
     */
    public function setEnableTracklink(int $enableTracklink): static;

    /**
     * Get customerGroups
     *
     * @return mixed|array|null
     */
    public function getCustomerGroups(): mixed;

    /**
     * Set customerGroups
     *
     * @param mixed|array $customerGroups
     * @return $this
     */
    public function setCustomerGroups(mixed $customerGroups): static;

    /**
     * Get design_fields
     *
     * @return FieldDesignInterface[]|mixed|array|null
     */
    public function getDesignFields(): mixed;

    /**
     * Set design_fields
     *
     * @param array|FieldDesignInterface[] $designFields
     * @return $this
     */
    public function setDesignFields(array $designFields): static;
}
