<?php

namespace Swissup\SeoImages\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator;
use Swissup\SeoImages\Model\ResourceModel\Entity as ImageResource;

class Flush extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_SeoImages::index_flush';

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var ImageResource
     */
    protected $imageResource;

    /**
     * @param Context       $context
     * @param ImageResource $imageResource
     * @param Validator     $formKeyValidator
     */
    public function __construct(
        Context $context,
        ImageResource $imageResource,
        Validator $formKeyValidator
    ) {
        parent::__construct($context);
        $this->imageResource = $imageResource;
        $this->formKeyValidator = $formKeyValidator;
    }

    /**
     * Flush action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage( __('Your session has expired'));

            return $resultRedirect->setRefererUrl();
        }

        try {
            $this->imageResource->cleanCached();
            $this->messageManager->addSuccessMessage(__('SEO Images Names cache cleaned.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setRefererOrBaseUrl();
    }
}
