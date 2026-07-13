<?php

namespace Swissup\SeoCrossLinks\Controller\Adminhtml\Link;

use Magento\Backend\App\Action\Context;
use Swissup\SeoCrossLinks\Model\LinkFactory;

class Delete extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_SeoCrossLinks::link_delete';

    /**
     * @var LinkFactory
     */
    protected $linkFactory;

    /**
     * @param Context $context
     * @param LinkFactory $linkFactory
     */
    public function __construct(
        Context $context,
        LinkFactory $linkFactory
    ) {
        $this->linkFactory = $linkFactory;
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

        $id = $this->getRequest()->getParam('link_id');

        if ($id) {
            try {
                $model = $this->linkFactory->create();

                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('You deleted the item.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['link_id' => $id]);
            }
        }
        $this->messageManager->addError(__('We can\'t find an item to delete.'));

        return $resultRedirect->setPath('*/*/');
    }
}
