<?php
declare(strict_types=1);

namespace Ceb\Installments\Controller\Adminhtml;

abstract class Installment extends \Magento\Backend\App\Action
{

    public const ADMIN_RESOURCE = 'Ceb_Installments::top_level';
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function initPage($resultPage)
    {
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE)
            ->addBreadcrumb(__('Ceb'), __('Ceb'))
            ->addBreadcrumb(__('Installment'), __('Installment'));
        return $resultPage;
    }
}

