<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Controller\Adminhtml\FormData;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Hexamarvel\FlexibleForm\Model\FormFactory;

class Index extends Action
{
    const ADMIN_RESOURCE = 'Hexamarvel_FlexibleForm::view_form';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param FormFactory $formFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        FormFactory $formFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->formFactory       = $formFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->_view->loadLayout();
        $resultPage = $this->resultPageFactory->create();
        $formId = $this->getRequest()->getParam('form_id');
        $title = $this->formFactory->create()->load($formId)->getTitle();
        $resultPage->setActiveMenu('Hexamarvel_FlexibleForm::view_form');
        $resultPage->addBreadcrumb(__('View Results'), __('View Results'));
        $resultPage->getConfig()->getTitle()->prepend('Results ' . $title);

        $this->_addContent($this->_view->getLayout()->createBlock('Hexamarvel\FlexibleForm\Block\Adminhtml\FormData\Grid'));
        $this->_view->renderLayout();
    }
}
