<?php
declare(strict_types=1);

namespace Ecommerce66\Brands\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Helper\Data as HelperCatalog;
use Ecommerce66\Brands\Helper\Data as HelperData;
use Ecommerce66\Brands\Model\ResourceModel\BrandDetails\CollectionFactory;

class Base extends Template
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var CollectionFactory
     */
    protected $brandsFactory;

    /**
     * @var Resolver
     */
    protected $layerResolver;

    /**
     * @var HelperCatalog
     */
    protected $helperCatalog;

    /**
     * Base constructor.
     *
     * @param Context           $context
     * @param Resolver          $layerResolver
     * @param HelperData        $helperData
     * @param HelperCatalog     $helperCatalog
     * @param CollectionFactory $brandDetailsFactory
     * @param array             $data
     */
    public function __construct(
        Context $context,
        Resolver $layerResolver,
        HelperData $helperData,
        HelperCatalog $helperCatalog,
        CollectionFactory $brandDetailsFactory,
        array $data = []
    ) {
        $this->layerResolver = $layerResolver;
        $this->helperData = $helperData;
        $this->helperCatalog = $helperCatalog;
        $this->brandsFactory = $brandDetailsFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseUrl()
    {
        return $this->helperData->getBaseUrl();
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaUrl()
    {
        return $this->helperData->getBaseMediaUrl();
    }

    /**
     * @param string $cmsContent
     *
     * @return string
     * @throws \Exception
     */
    public function renderCms($cmsContent = '')
    {
        return $this->helperData->filterCms($cmsContent);
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getCurrentProduct()
    {
        return $this->helperCatalog->getProduct();
    }
}
