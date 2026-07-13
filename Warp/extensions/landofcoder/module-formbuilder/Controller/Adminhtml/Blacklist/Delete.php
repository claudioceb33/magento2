<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_Formbuilder
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\Formbuilder\Controller\Adminhtml\Blacklist;

use Lof\Formbuilder\Controller\Adminhtml\Blacklist;
use Magento\Backend\Model\View\Result\Redirect;

class Delete extends Blacklist
{
    public const ADMIN_RESOURCE = 'Lof_Formbuilder::blacklist_delete';

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('blacklist_id');
        if ($id) {
            try {
                $model = $this->_objectManager->create(\Lof\Formbuilder\Model\Blacklist::class);
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the blacklist.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/', ['blacklist_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a blacklist to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
