<?php
namespace Ceb\ButtonSystem\Controller\Adminhtml\Action;

class Action extends \Magento\Backend\App\Action
{
    
    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Namespace\Module\Helper\Data $dataHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context
    ) {
       
        parent::__construct($context);
    }
    
    /**
     * Optimize action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        set_time_limit(18000);
        
        try {
            $this->messageManager->addSuccessMessage(__('Actualizacion exitosa.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        
        return $resultRedirect->setPath('adminhtml/system_config/edit',['section' => 'mysection']);
    }
}