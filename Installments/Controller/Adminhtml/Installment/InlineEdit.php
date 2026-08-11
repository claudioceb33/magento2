<?php
declare(strict_types=1);

namespace Ceb\Installments\Controller\Adminhtml\Installment;

class InlineEdit extends \Magento\Backend\App\Action
{

    protected $jsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
    ) {
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
            if (count($postItems)) {
                $messages = [];
                $error = false;
                foreach (array_keys($postItems) as $modelid) {
                    /** @var \Ceb\Installments\Model\Installment $model */
                    $model = $this->_objectManager->create(\Ceb\Installments\Model\Installment::class)->load($modelid);
                    try {
                        $model->setData(array_merge($model->getData(), $postItems[$modelid])); // phpcs:ignore
                        $model->save();
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

