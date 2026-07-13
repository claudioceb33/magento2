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

namespace Lof\Formbuilder\Block;

use Lof\Formbuilder\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Url;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Form extends Template
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Data
     */
    protected $formHelper;

    /**
     * @var \Lof\Formbuilder\Model\Form
     */
    protected $form;

    /**
     * @var Url
     */
    protected $url;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var mixed|array
     */
    protected mixed $fields = [
        "text" => "fields/text.phtml",
        "website" => "fields/website.phtml",
        "radio" => "fields/radio.phtml",
        "dropdown" => "fields/dropdown.phtml",
        "paragraph" => "fields/textarea.phtml",
        "email" => "fields/email.phtml",
        "date" => "fields/date.phtml",
        "time" => "fields/time.phtml",
        "checkboxes" => "fields/checkboxes.phtml",
        "number" => "fields/number.phtml",
        "price" => "fields/price.phtml",
        "section_break" => "fields/section_break.phtml",
        "address" => "fields/address.phtml",
        "file_upload" => "fields/file.phtml",
        "multifile_upload" => "fields/multi_files.phtml",
        "model_dropdown" => "fields/model_dropdown.phtml",
        "subscription" => "fields/subscription.phtml",
        "rating" => "fields/rating.phtml",
        "google_map" => "fields/google_map.phtml",
        "html" => "fields/html.phtml",
        "product_field" => "fields/product_field.phtml",
        "digital_signature" => "fields/digital_signature.phtml",
        "phone" => "fields/phone.phtml",
        "image" => "fields/image.phtml"
    ];

    /**
     * @var mixed|array
     */
    protected mixed $inlineLabelFields = [
        "text" => "inline_label_fields/text.phtml",
        "website" => "inline_label_fields/website.phtml",
        "radio" => "inline_label_fields/radio.phtml",
        "dropdown" => "inline_label_fields/dropdown.phtml",
        "paragraph" => "inline_label_fields/textarea.phtml",
        "email" => "inline_label_fields/email.phtml",
        "date" => "inline_label_fields/date.phtml",
        "time" => "inline_label_fields/time.phtml",
        "checkboxes" => "inline_label_fields/checkboxes.phtml",
        "number" => "inline_label_fields/number.phtml",
        "price" => "inline_label_fields/price.phtml",
        "section_break" => "inline_label_fields/section_break.phtml",
        "address" => "inline_label_fields/address.phtml",
        "file_upload" => "inline_label_fields/file.phtml",
        "multifile_upload" => "inline_label_fields/multi_files.phtml",
        "model_dropdown" => "inline_label_fields/model_dropdown.phtml",
        "subscription" => "inline_label_fields/subscription.phtml",
        "rating" => "inline_label_fields/rating.phtml",
        "google_map" => "inline_label_fields/google_map.phtml",
        "html" => "inline_label_fields/html.phtml",
        "product_field" => "inline_label_fields/product_field.phtml",
        "digital_signature" => "inline_label_fields/digital_signature.phtml",
        "phone" => "inline_label_fields/phone.phtml",
        "image" => "inline_label_fields/image.phtml"
    ];

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Data $helper
     * @param \Lof\Formbuilder\Model\Form $form
     * @param Url $url
     * @param RequestInterface $request
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $helper,
        \Lof\Formbuilder\Model\Form $form,
        Url $url,
        RequestInterface $request,
        Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->formHelper = $helper;
        $this->form = $form;
        $this->url = $url;
        $this->request = $request;
        $this->customerSession = $customerSession;
    }

    /**
     * @param $form
     * @return $this
     */
    public function setCurrentForm($form)
    {
        $this->form = $form;
        return $this;
    }

    /**
     * @return \Lof\Formbuilder\Model\Form|mixed|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCurrentForm()
    {
        if (isset($this->form)) {
            $store = $this->_storeManager->getStore();

            $form = $this->registry->registry('current_form') ?? $this->form;
            $formId = $this->request->getParam("form_id");
            if (!$form && $formId) {
                $form = $this->form->setStore($store)->load((int)$formId);
                if (!$form->getId()) {
                    return null;
                }
                $customerGroups = $form->getData('customergroups');
                $customerGroupId = $this->customerSession->getCustomerGroupId();
                $customerGroups = !empty($customerGroups) ? $customerGroups : [0];

                if (!in_array(0, $customerGroups) && !$this->customerSession->isLoggedIn()) {
                    return null;
                }

                if (!in_array($customerGroupId, $customerGroups)) {
                    return null;
                }

                if (!$form->getStatus()) {
                    return null;
                }
            }
            $this->form = $form;
        }
        return $this->form;
    }

    /**
     * @param $form
     * @param $fieldType
     * @param $fieldData
     * @param bool $isInlineLabel
     * @return string
     * @throws LocalizedException
     */
    public function getField($form, $fieldType, $fieldData, bool $isInlineLabel = false): string
    {
        if ($isInlineLabel) {
            $fieldArr = $this->inlineLabelFields;
        } else {
            $fieldArr = $this->fields;
        }

        $html = '';
        if (array_key_exists($fieldType, $fieldArr)) {
            $template = $fieldArr[$fieldType];
            if (isset($fieldData['custom_template']) && $fieldData['custom_template'] != '') {
                $template = $fieldData['custom_template'];
            }
            $html = $this->getLayout()
                ->createBlock(Field::class)
                ->setData('field_data', $fieldData)
                ->setForm($form)
                ->setTemplate($template)
                ->toHtml();
        }
        return $html;
    }

    /**
     * @param $key
     * @param string $default
     * @return array|mixed|string|null
     * @throws NoSuchEntityException
     */
    public function getConfig($key, string $default = '')
    {
        if ($this->hasData($key)) {
            return $this->getData($key);
        }
        $result = $this->formHelper->getConfig($key);
        if ($result != null) {
            return $result;
        }
        return $default;
    }

    /**
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->url->getCurrentUrl();
    }
}
