<?php

declare(strict_types=1);

namespace Ceb\Installments\Controller\Adminhtml\Installment;

use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Ceb_Installments::top_level';

    /** @var \Ceb\Installments\Model\InstallmentFactory */
    protected $installmentFactory;

    /** @var \Ceb\Installments\Model\ResourceModel\Installment */
    protected $installmentResource;

    protected $dataPersistor;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param \Ceb\Installments\Model\InstallmentFactory $installmentFactory
     * @param \Ceb\Installments\Model\ResourceModel\Installment $installmentResource
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Ceb\Installments\Model\InstallmentFactory $installmentFactory,
        \Ceb\Installments\Model\ResourceModel\Installment $installmentResource
    ) {
        $this->installmentFactory = $installmentFactory;
        $this->installmentResource = $installmentResource;
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
            $id = $this->getRequest()->getParam('installment_id');

            $model = $this->installmentFactory->create();
            if ($id) {
                $this->installmentResource->load($model, $id);
            }
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Installment no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            $model->setData($data);

            try {
                $this->installmentResource->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the Installment.'));
                $this->dataPersistor->clear('ceb_installments_installment');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['installment_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the Installment.')
                );
            }

            $this->dataPersistor->set('ceb_installments_installment', $data);
            return $resultRedirect->setPath('*/*/edit', ['installment_id' => $id]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
