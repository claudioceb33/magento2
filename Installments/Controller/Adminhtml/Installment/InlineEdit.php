<?php

declare(strict_types=1);

namespace Ceb\Installments\Controller\Adminhtml\Installment;

class InlineEdit extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Ceb_Installments::top_level';

    /** @var \Ceb\Installments\Model\InstallmentFactory */
    protected $installmentFactory;

    /** @var \Ceb\Installments\Model\ResourceModel\Installment */
    protected $installmentResource;

    protected $jsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ceb\Installments\Model\InstallmentFactory $installmentFactory
     * @param \Ceb\Installments\Model\ResourceModel\Installment $installmentResource
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ceb\Installments\Model\InstallmentFactory $installmentFactory,
        \Ceb\Installments\Model\ResourceModel\Installment $installmentResource
    ) {
        $this->installmentFactory = $installmentFactory;
        $this->installmentResource = $installmentResource;
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * Inline edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $messages[] = __('Please correct the data sent.');
        $error = true;

        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (is_array($postItems) && $postItems) {
                $messages = [];
                $error = false;
                foreach (array_keys($postItems) as $modelid) {
                    /** @var \Ceb\Installments\Model\Installment $model */
                    $model = $this->installmentFactory->create();
                    try {
                        $this->installmentResource->load($model, $modelid);
                        if (!$model->getId()) {
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __('This Installment no longer exists.')
                            );
                        }
                        if (!is_array($postItems[$modelid])) {
                            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid row data.'));
                        }
                        unset($postItems[$modelid]['installment_id']);
                        $model->addData($postItems[$modelid]);
                        $this->installmentResource->save($model);
                    } catch (\Exception $e) {
                        $messages[] = "[Installment ID: {$modelid}]  {$e->getMessage()}";
                        $error = true;
                    }
                }
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }
}
