<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Controller\Adminhtml\Form;

class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Hexamarvel_FlexibleForm::view_form';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory = false;

    /**
     * @param \Magento\Backend\App\Action\Context $context,
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return obj $resultPage
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Hexamarvel_FlexibleForm::view_form');
        $resultPage->addBreadcrumb(__('Manage Forms'), __('Manage Forms'));
        $resultPage->getConfig()->getTitle()->prepend(__('Forms'));

        return $resultPage;
    }
}
