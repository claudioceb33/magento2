<?php
namespace Swissup\ProLabels\Controller\Adminhtml\Label;

use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Swissup\ProLabels\Model\ResourceModel\Label as ResourceLabel;
use Swissup\ProLabels\Model\LabelFactory;

class Save extends Action
{
    /**
     * Array of image uploaders for product label and category label
     *
     * @var array
     */
    protected $imageUploader;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var LabelFactory
     */
    protected $labelFactory;

    /**
     * @var ResourceLabel
     */
    protected $resourceLabel;

    /**
     * @param Action\Context         $context
     * @param DataPersistorInterface $dataPersistor
     * @param LabelFactory           $labelFactory
     * @param ResourceLabel          $resourceLabel
     * @param array                  $imageUploader
     */
    public function __construct(
        Action\Context $context,
        DataPersistorInterface $dataPersistor,
        LabelFactory $labelFactory,
        ResourceLabel $resourceLabel,
        $imageUploader = []
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->labelFactory = $labelFactory;
        $this->resourceLabel = $resourceLabel;
        $this->imageUploader = $imageUploader;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Swissup_ProLabels::save');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var array $data */
        $data = $this->getRequest()->getPostValue();
        $redirectBack = $this->getRequest()->getParam('back', false);
        $this->dataPersistor->set('prolabels_label', $data);
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            /** @var \Swissup\ProLabels\Model\Label $model */
            $model = $this->labelFactory->create();
            $id = $this->getRequest()->getParam('label_id');
            if ($id) {
                $model->load($id);
            }

            if (isset($data['rule'])) {
                $data['conditions'] = $data['rule']['conditions'];
                unset($data['rule']);
            }

            $model->loadPost($data);
            /*
             ** Label Images Upload
             */
            foreach ($this->imageUploader as $mode => $imageUploader) {
                $imageName = '';
                if (isset($data["{$mode}_image"])
                    && is_array($data["{$mode}_image"])
                ) {
                    $imageName = isset($data["{$mode}_image"][0]['name'])
                        ? $data["{$mode}_image"][0]['name']
                        : '';
                    if (isset($data["{$mode}_image"][0]['tmp_name'])) {
                        try {
                            $imageUploader->moveFileFromTmp($imageName, true);
                        } catch (\Exception $e) {
                            //
                        }
                    }
                }
                $model->setData("{$mode}_image", $imageName);
            }

            $model->setCustomerGroups($data['customer_groups']);
            $model->setStoreId($data['store_id']);
            try {
                $model->save();
                $this->messageManager->addSuccess(__('Label has been saved.'));
                $this->dataPersistor->clear('prolabels_label');

                if ($redirectBack == 'duplicate') {
                    $duplicatedModel = $this->resourceLabel->duplicate($model);
                    $this->messageManager->addSuccess(__('You duplicated the label.'));

                    return $resultRedirect->setPath(
                        '*/*/edit',
                        [
                            'label_id' => $duplicatedModel->getId(),
                            'back' => null,
                            '_current' => true
                        ]
                    );
                } elseif ($redirectBack) {
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        [
                            'label_id' => $model->getId(),
                            '_current' => true
                        ]
                    );
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while saving the label.')
                );
            }

            return $resultRedirect->setPath(
                '*/*/edit',
                [
                    'label_id' => $this->getRequest()->getParam('label_id')
                ]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }
}
