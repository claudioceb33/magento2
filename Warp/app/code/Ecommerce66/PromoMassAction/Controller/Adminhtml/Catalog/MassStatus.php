<?php

namespace Ecommerce66\PromoMassAction\Controller\Adminhtml\Catalog;

use \Magento\Framework\App\ResponseInterface;
use \Magento\Framework\Controller\Result\Redirect;
use \Magento\Framework\Controller\ResultInterface;

class MassStatus extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog
{
    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $ids = $this->getRequest()->getPost('ids');
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('catalog_rule/promo_catalog/');

        if (!is_array($ids)) {
            $this->messageManager->addError(__('Please select item(s).'));
            return $resultRedirect;
        }

        try {
            foreach ($ids as $id) {
                $this->_objectManager->create(\Magento\CatalogRule\Model\Rule::class)
                    ->load($id)
                    ->setIsActive($this->getRequest()->getPost('status'))
                    ->save();
            }
            $this->messageManager->addSuccess(__('Total of %1 rule(s) were successfully updated.', count($ids)));

        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        return $resultRedirect;
    }
}
