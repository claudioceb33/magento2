<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Controller\Adminhtml\Field;

use Magento\Framework\App\ObjectManager;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Hexamarvel\FlexibleForm\Model\FieldFactory
     */
    protected $fieldFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Hexamarvel\FlexibleForm\Model\FieldFactory $fieldFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Hexamarvel\FlexibleForm\Model\FieldFactory $fieldFactory
    ) {
        parent::__construct($context);
        $this->fieldFactory = $fieldFactory;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost('field_configuration');

        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$data) {
            $this->_redirect('*/form/index');
            return;
        }
        try {
            if (empty($data['id'])) {
                unset($data['id']);
            }

            $rowData = $this->fieldFactory->create();
            $rowData->setData($data);
            if (isset($data['id'])) {
                $rowData->setId($data['id']);
            }

            $rowData->save();
            $this->messageManager->addSuccess(__('Field has been successfully saved.'));
            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['field_id' => $rowData->getId(), 'form_id' => $rowData->getFormId()]);
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $resultRedirect->setPath('*/form/edit', ['id' => $data['form_id']]);
    }
}
