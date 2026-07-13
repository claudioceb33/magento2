<?php

namespace Swissup\SeoTemplates\Controller\Adminhtml\Template;

use Magento\Backend\App\Action\Context;
use Swissup\SeoTemplates\Model\TemplateFactory;

class Delete extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_SeoTemplates::template_delete';

    /**
     * @var TemplateFactory
     */
    protected $templateFactory;

    /**
     * @param Context $context
     * @param TemplateFactory $templateFactory
     */
    public function __construct(
        Context $context,
        TemplateFactory $templateFactory
    ) {
        $this->templateFactory = $templateFactory;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->templateFactory->create();
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('You\'ve deleted template.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        $this->messageManager->addError(__('We can\'t find template to delete.'));

        return $resultRedirect->setPath('*/*/');
    }
}
