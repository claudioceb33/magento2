<?php
declare(strict_types=1);

namespace Ecommerce66\Brands\Controller\Adminhtml;

abstract class Branddetails extends \Magento\Backend\App\Action
{

    public const ADMIN_RESOURCE = 'Ecommerce66_Brands::top_level';

    /**
     * @var \Magento\Framework\Registry
     */
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
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ecommerce66_Brands::brand_details_view');
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
            ->addBreadcrumb(__('Ecommerce66'), __('Ecommerce66'))
            ->addBreadcrumb(__('Brand Details'), __('Brand Details'));
        return $resultPage;
    }
}
