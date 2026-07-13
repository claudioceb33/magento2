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

namespace Lof\Formbuilder\Controller\Form;

use Lof\Formbuilder\Model\Model;
use Magento\Customer\Controller\AccountInterface;
use Magento\Directory\Helper\Data;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class State extends Action
{
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param Model $model
     * @param Escaper $escaper
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        Model $model,
        Escaper $escaper,
        Data $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry     = $registry;
        $this->model            = $model;
        $this->escaper          = $escaper;
        $this->helper           = $helper;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        header('Content-Type: text/javascript');
        $post = $this->getRequest()->getPost();
        $fieldName = $post['field_name'];
        $scopeHelper = $this->helper;
        $regionsData = $scopeHelper->getRegionData();
        $countries = $scopeHelper->getCountryCollection()->toOptionArray(false);
        foreach ($countries as $country) {
            if ($country['label'] == $post['country_id']) {
                $code = $country['value'];
            }
        }
        $output = [];
        $dataReturn = '';

        if (isset($code)) {
            if (array_key_exists($code, $regionsData)) {
                foreach ($regionsData[$code] as $key => $region) {
                    $output[$code]['regions'][$key]['code'] = $region['code'];
                    $output[$code]['regions'][$key]['name'] = $region['name'];
                }
                if ($output) {
                    $dataReturn .= '<select id="' . $fieldName . '"class="required-entry" name="' . $fieldName . '">';
                    $dataReturn .= '<option value="">-- ' . __("Please Select") . ' --</option>';
                    foreach ($output[$code]['regions'] as $key => $_output) {
                        $dataReturn .= '<option value="' . $_output['name'] . '">' . $_output['name'] . '</option>';
                    }
                    $dataReturn .= '<select>';
                    $dataReturn .= '<label for="' . $fieldName . '">' . __("State / Province / Region") . '</label>';
                }
            } else {
                $dataReturn .= '<input class="input-text validate-state" type="text" id="' .
                    $fieldName . '"class="required-entry" name="' . $fieldName . '" />';
                $dataReturn .= '<label for="' . $fieldName . '">' . __("State / Province / Region") . '</label>';
            }
        } else {
            $dataReturn .= '<input class="input-text validate-state" type="text" id="' .
                $fieldName . '"class="required-entry" name="' . $fieldName . '" />';
            $dataReturn .= '<label for="' . $fieldName . '">' . __("State / Province / Region") . '</label>';
        }
        echo  json_encode($dataReturn);

        exit;
    }
}
