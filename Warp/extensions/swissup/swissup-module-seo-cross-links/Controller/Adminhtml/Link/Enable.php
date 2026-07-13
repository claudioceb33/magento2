<?php
namespace Swissup\SeoCrossLinks\Controller\Adminhtml\Link;

use \Magento\Backend\App\Action\Context;
use \Swissup\SeoCrossLinks\Model\LinkFactory;

class Enable extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_SeoCrossLinks::link_save';

     /**
      * @var string
     */
    protected $msgSuccess = 'Link "%1" was enabled.';

    /**
     * @var integer
     */
    protected $newStatusCode = 1;

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
     * Enable action
     *
     * @return \Magento\Backend\Model\View\Result\Page
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
                $model->setIsActive($this->newStatusCode);
                $model->save();
                $this->messageManager->addSuccess(__($this->msgSuccess, $model->getTitle()));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['link_id' => $id]);
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
