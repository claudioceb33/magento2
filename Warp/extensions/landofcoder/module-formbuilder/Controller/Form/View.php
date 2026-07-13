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

use Lof\Formbuilder\Model\FormFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class View extends Action
{
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param FormFactory $formFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        FormFactory $formFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $registry;
        $this->formFactory = $formFactory;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $page = $this->resultPageFactory->create();
        $formId = $this->getRequest()->getParam("form_id");
        $form = $this->coreRegistry->registry('current_form');
        if (!$form && $formId) {
            $form = $this->formFactory->create()->load((int)$formId);
            $this->coreRegistry->register("current_form", $form);
        }

        $page->addHandle(['type' => 'FFORMBUILDER_VIEW_' . $form->getFormId()]);
        if (($layoutUpdate = $form->getLayoutUpdateXml()) && @trim($layoutUpdate) != '') {
            $page->addUpdate($layoutUpdate);
        }

        $pageLayout = $form->getPageLayout();
        if ($pageLayout) {
            $page->getConfig()->setPageLayout($pageLayout);
        }

        return $page;
    }
}
