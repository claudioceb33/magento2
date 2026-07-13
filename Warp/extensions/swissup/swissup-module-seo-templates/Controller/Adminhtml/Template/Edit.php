<?php
namespace Swissup\SeoTemplates\Controller\Adminhtml\Template;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Edit extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_SeoTemplates::template_save';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Swissup\SeoTemplates\Model\TemplateFactory
     */
    protected $templateFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Swissup\SeoTemplates\Model\TemplateFactory $templateFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->templateFactory = $templateFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $templateModel = $this->templateFactory->create();
        if ($templateId = $this->getRequest()->getParam('id')) {
            $templateModel->load($templateId);

            if (!$templateModel->getId()) {
                $this->messageManager->addError(
                    __('Template with ID %1 no longer exists.', $templateId)
                );
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        } else {
            $templateModel->setData(
                'entity_type',
                $this->getRequest()->getParam('entity_type')
            )->setData(
                'data_name',
                $this->getRequest()->getParam('data_name')
            );
        }

        $this->registry->register('seotemplates_template', $templateModel);
        $this->registry->register(
            'seotemplates_template_type',
            $templateModel->getEntityType()
        );

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Swissup_SeoTemplates::template_index')
            ->addBreadcrumb(__('Metadata Templates'), __('Metadata Templates'));
        if ($templateId) {
            $resultPage->addBreadcrumb(
                __('Edit "%1"', $templateModel->getName()),
                __('Edit "%1"', $templateModel->getName())
            );
        } else {
            $resultPage->addBreadcrumb(
                __('Create template'),
                __('Create template')
            );
        }

        $resultPage->getConfig()->getTitle()->prepend(__('Metadata Templates'));
        if ($templateId) {
            $resultPage->getConfig()->getTitle()->prepend(
                __('Edit "%1"', $templateModel->getName())
            );
        } else {
            $resultPage->getConfig()->getTitle()
                ->prepend(__('Create template'));
        }

        return $resultPage;
    }
}
