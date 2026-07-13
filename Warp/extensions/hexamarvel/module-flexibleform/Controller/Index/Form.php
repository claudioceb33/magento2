<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Hexamarvel\FlexibleForm\Helper\Data as Helper;

class Form extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var PageFactory
     */
    protected $helper;

    /**
     * Form constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Helper $helper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Helper $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->helper            = $helper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $page = $this->resultPageFactory->create();
        $formTitle = $this->getRequest()->getParam('form_object')->getTitle();
        if ($this->helper->getConfig('hexaform/general/enable_breadcrumb')) {
            $breadcrumbs = $page->getLayout()->getBlock('breadcrumbs');
            $breadcrumbs->addCrumb('home', [
                'label' => __('Home'),
                'title' => __('Home'),
                'link' => $this->_url->getUrl('')
            ]);
            $breadcrumbs->addCrumb('form_link', [
                'label' => $formTitle,
                'title' => $formTitle,
            ]);
        }

        $page->getConfig()->getTitle()->set($formTitle);
        return $page;
    }
}
