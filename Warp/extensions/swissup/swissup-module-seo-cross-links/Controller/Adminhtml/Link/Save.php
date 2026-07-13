<?php

namespace Swissup\SeoCrossLinks\Controller\Adminhtml\Link;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Swissup\SeoCrossLinks\Model\LinkFactory;

class Save extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_SeoCrossLinks::link_save';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var LinkFactory
     */
    protected $linkFactory;

    /**
     * @param Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        LinkFactory $linkFactory,
        DataPersistorInterface $dataPersistor
    ) {
        $this->linkFactory = $linkFactory;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            if (isset($data['is_active']) && $data['is_active'] === 'true') {
                $data['is_active'] = 1;
            }
            if (empty($data['link_id'])) {
                $data['link_id'] = null;
            }

            $id = $this->getRequest()->getParam('link_id');
            /** @var \Swissup\SeoCrossLinks\Model\Link $model */
            $link = $this->linkFactory->create()->load($id);


            if (!$link->getId() && $id) {
                $this->messageManager->addError(__('This link no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            $link->addData($data);

            try {
                $link->save();
                $this->messageManager->addSuccess(__('You saved link.'));
                $this->dataPersistor->clear('seocrosslinks_link');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['link_id' => $link->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the link.'));
            }

            $this->dataPersistor->set('seocrosslinks_link', $data);
            return $resultRedirect->setPath('*/*/edit', ['link_id' => $id]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
