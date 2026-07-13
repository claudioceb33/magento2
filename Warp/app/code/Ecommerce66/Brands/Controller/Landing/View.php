<?php

namespace Ecommerce66\Brands\Controller\Landing;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Ecommerce66\Brands\Model\BrandDetailsFactory;
use Ecommerce66\Brands\Helper\Data as HelperData;

class View extends Action
{
    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var BrandDetailsFactory
     */
    protected $brandsFactory;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * View constructor.
     *
     * @param Context             $context
     * @param ResultFactory       $resultFactory
     * @param PageFactory         $resultPageFactory
     * @param BrandDetailsFactory $brandDetailsFactory
     * @param HelperData          $helperData
     */
    public function __construct(
        Context $context,
        ResultFactory $resultFactory,
        PageFactory $resultPageFactory,
        BrandDetailsFactory $brandDetailsFactory,
        HelperData $helperData
    ) {
        parent::__construct($context);
        $this->resultFactory = $resultFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->brandsFactory = $brandDetailsFactory;
        $this->helperData = $helperData;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $landingCode = $this->getRequest()->getParam('path');
        $landingCode = !empty($landingCode) ? $landingCode : 'index';

        $brand = $this->helperData
            ->getBrandList(['landing_url_key' => $landingCode])
            ->getFirstItem();

        if ((!$brand->getId() || !(bool)(int)$brand->getLandingActive()) && $landingCode != 'index') {
            // if landing does not exists or is not active, and is not brands index
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('noroute');
            return $resultForward;
        }

        // todo: make configurable
        $title = __('Brands');
        $keywords = __('Brands listing');
        $description = __('Brands listing');
        $resultPage = $this->resultPageFactory->create();
        if ($landingCode != 'index') {
            $title = $brand->getLandingName();
            $keywords = $brand->getLandingMetaKey();
            $description = $brand->getLandingMetaDesc();
        }
        $resultPage->getConfig()->getTitle()->prepend($title);
        $resultPage->getConfig()->setKeywords($keywords);
        $resultPage->getConfig()->setDescription($description);
        $blockB = $resultPage->getLayout()->getBlock('brand_brands_index');
        $blockB->setData('brand', $brand);
        $blockL = $resultPage->getLayout()->getBlock('brand_landing_view');
        $blockL->setData('brand', $brand);

        return $resultPage;
    }
}
