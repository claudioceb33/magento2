<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Controller\Adminhtml\FormData;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class Delete extends Action implements HttpPostActionInterface
{
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param FormFactory $formFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Hexamarvel\FlexibleForm\Model\ResourceModel\FormData\CollectionFactory $collectionFactory,
        \Hexamarvel\FlexibleForm\Model\FormFactory $dataFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->dataFactory = $dataFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $collection = $this->getCollection($this->collectionFactory->create());
        $dataDeleted = 1;
        $dataDeletedError = 0;
        $formId = 1;
        if ($collection) {
            /*foreach ($collection as $key => $values) {
                try {
                    $data = $this->dataFactory->create();
                    $data->load($data->getId());
                    $data->delete();
                    $dataDeleted++;
                } catch (LocalizedException $exception) {
                    $dataDeletedError++;
                }
            }*/
        }

        if ($dataDeleted) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', $dataDeleted)
            );
        }

        if ($dataDeletedError) {
            $this->messageManager->addErrorMessage(
                __(
                    'A total of %1 record(s) haven\'t been deleted. Please see server logs for more details.',
                    $dataDeletedError
                )
            );
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath(
            '*/form/index'
        );
    }

    /**
     * @param obj $collection
     */
    private function getCollection($collection)
    {
        $selected = $this->getRequest()->getParam('selected');
        try {
            if (is_array($selected) && !empty($selected)) {
                $collection->addFieldToFilter($collection->getResource()->getIdFieldName(), ['in' => $selected]);
            } else {
                return;
            }
        } catch (\Exception $e) {
            return;
        }

        return $collection;
    }
}
