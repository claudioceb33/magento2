<?php

namespace Ecommerce66\PromoMassAction\Controller\Adminhtml\Catalog;

use \Magento\Framework\App\ResponseInterface;
use \Magento\Framework\Controller\Result\Redirect;
use \Magento\Framework\Controller\ResultInterface;

class MassDelete extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog
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
            $errorCount = 0;
            foreach ($ids as $id) {
                $model = $this->_objectManager->create(\Magento\CatalogRule\Model\Rule::class)->load($id);
                if ($model->getIsActive()) {
                    $this->messageManager
                        ->addError(__('The item %1 cant be deleted due is active.', $model->getId()));
                    $errorCount++;
                    continue;
                }
                $model->delete();
            }
            $this->messageManager->addSuccess(
                __('Total of %1 rule(s) were successfully deleted.', count($ids)-$errorCount)
            );

        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        return $resultRedirect;
    }
}
