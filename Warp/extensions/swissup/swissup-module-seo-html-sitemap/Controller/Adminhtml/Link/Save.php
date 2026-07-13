<?php
namespace Swissup\SeoHtmlSitemap\Controller\Adminhtml\Link;

use \Magento\Backend\App\Action;
use \Magento\TestFramework\ErrorLog\Logger;
use \Swissup\SeoHtmlSitemap\Model\LinkFactory;

class Save extends Action
{
    private $linkFactory;
    /**
     * Admin resource
     */
    const ADMIN_RESOURCE = 'Swissup_SeoHtmlSitemap::link_save';

    /**
     * @param Action\Context $context
     */
    public function __construct(Action\Context $context, LinkFactory $linkFactory)
    {
        $this->linkFactory = $linkFactory;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            /** @var \Swissup\SeoHtmlSitemap\Model\Link $model */
            $model = $this->linkFactory->create();

            $id = $this->getRequest()->getParam('link_id');

            if ($id) {
                $model->load($id);
            }

            $model->setData($data);

            $this->_eventManager->dispatch(
                'link_prepare_save',
                ['link' => $model, 'request' => $this->getRequest()]);

            try {
                $model->save();
                $this->messageManager->addSuccess(__('Link has been saved.'));
                $this->_getSession()->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit',
                        ['link_id' => $model->getId(), '_current' => true]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e,
                    __('Something went wrong while saving the link.'));
            }

            $this->_getSession()->setFormData($data);

            return $resultRedirect->setPath('*/*/edit',
                ['link_id' => $this->getRequest()->getParam('link_id')]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
