<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Controller\Adminhtml\FieldSet;

use Magento\Framework\App\ObjectManager;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Hexamarvel\FlexibleForm\Model\FieldSetFactory
     */
    protected $fieldSetFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Hexamarvel\FlexibleForm\Model\FieldSetFactory $fieldSetFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Hexamarvel\FlexibleForm\Model\FieldSetFactory $fieldSetFactory
    ) {
        parent::__construct($context);
        $this->fieldSetFactory = $fieldSetFactory;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost('fieldset_configuration');
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$data) {
            $this->_redirect('*/*/edit', ['fieldset_id' => $data['id'], 'form_id' => $data['form_id']]);
            return;
        }

        try {
            if (empty($data['id'])) {
                unset($data['id']);
            }

            $rowData = $this->fieldSetFactory->create();
            $rowData->setData($data);
            if (isset($data['id'])) {
                $rowData->setId($data['id']);
            }

            $rowData->save();
            $this->messageManager->addSuccess(__('FieldSet has been successfully saved.'));
            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['fieldset_id' => $rowData->getId(), 'form_id' => $rowData->getFormId()]);
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $resultRedirect->setPath('*/form/edit', ['id' => $data['form_id']]);
    }
}
