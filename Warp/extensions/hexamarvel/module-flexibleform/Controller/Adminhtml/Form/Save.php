<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Controller\Adminhtml\Form;

use Magento\Framework\App\ObjectManager;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Hexamarvel\FlexibleForm\Model\FormFactory
     */
    protected $formFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Hexamarvel\FlexibleForm\Model\FormFactory $formFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Hexamarvel\FlexibleForm\Model\FormFactory $formFactory
    ) {
        parent::__construct($context);
        $this->formFactory = $formFactory;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $dataForm = $this->getRequest()->getPost('form_configuration');
        $dataGeneral = $this->getRequest()->getPost('genral_settings');
        $dataEmail = $this->getRequest()->getPost('email_settings');
        $resultRedirect = $this->resultRedirectFactory->create();
        $dataGeneral['store'] = implode(',', $dataGeneral['store']);
        $finatData = array_merge($dataForm, $dataGeneral, $dataEmail);
        if (!$finatData) {
            $this->_redirect('*/*/index');
            return;
        }
        try {
            $form = $this->formFactory->create()->load($dataForm['url_key'], 'url_key');
            $alreadyAvailable = false;
            if (isset($dataForm['id'])) {
                if ($form->getId() && $form->getId() != $dataForm['id']) {
                    $alreadyAvailable = true;
                }
            } elseif ($form->getId()) {
                $alreadyAvailable = true;
            }

            if ($alreadyAvailable && !empty($dataForm['url_key'])) {
                $this->messageManager->addError(
                    __(
                        'Form URL key must be unique. "%1" is already available.',
                        $dataForm['url_key']
                    )
                );
                if (isset($dataForm['id'])) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $dataForm['id']]);
                }
                return $resultRedirect->setPath('*/*/edit');
            }

            $rowData = $this->formFactory->create();
            $rowData->setData($finatData);
            if (isset($dataForm['id'])) {
                $rowData->setId($dataForm['id']);
            }

            $rowData->save();
            $this->messageManager->addSuccess(__('Form has been successfully saved.'));
            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['id' => $rowData->getId()]);
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $resultRedirect->setPath('*/*/index');
    }
}
