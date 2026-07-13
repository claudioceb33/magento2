<?php
declare(strict_types=1);

namespace Ecommerce66\Brands\Helper;

use Zend_Db_Expr;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Catalog\Model\Product\Url as ProductUrl;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Ecommerce66\Brands\Model\ResourceModel\BrandDetails\CollectionFactory;
use Ecommerce66\Brands\Model\BrandDetails;

class Data extends AbstractHelper
{
    protected const BRAND_ATTR_CODE = 'brand';
    protected const ROUTES_PATH     = 'ecommerce66/routes/';
    protected const ROUTES_LANDING  = 'ecommerce66_brand_landing';
    protected const CONFIG_PATH     = 'brands/settings/';
    protected const CONFIG_SLIDER_CMS  = 'show_slider_in_cms';
    protected const CONFIG_SLIDER_PLP  = 'show_slider_in_plp';
    protected const CONFIG_SLIDER_PDP  = 'show_slider_in_pdp';
    protected const CONFIG_SLIDER_CART = 'show_slider_in_cart';
    protected const CONFIG_SHOW_IN_SLIDER   = 'show_in_slider';
    protected const CONFIG_SLIDER_LINK_TYPE = 'slider_link_type';
    protected const CONFIG_SLIDER_LINK_CID  = 'slider_link_category';

    /**
     * Default value if ecommerce66/routes/ecommerce66_brand_landing is empty
     */
    protected const DEFAULT_LANDING = 'brand-landing';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $productAttributeRepository;

    /**
     * @var ProductAttributeInterface
     */
    protected $brandAttribute;

    /**
     * @var CollectionFactory
     */
    protected $brandsFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var FilterProvider
     */
    protected $filterProvider;

    /**
     * @var array
     */
    protected $brandUrlDetails = [];

    /**
     * @var ProductUrl
     */
    protected $productUrl;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * Data constructor.
     *
     * @param Context                             $context
     * @param ScopeConfigInterface                $scopeConfig
     * @param Json                                $json
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param StoreManagerInterface               $storeManager
     * @param FilterProvider                      $filterProvider
     * @param CollectionFactory                   $brandDetailsFactory
     * @param ProductUrl                          $productUrl
     * @param CategoryRepositoryInterface         $categoryRepository
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Json $json,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        StoreManagerInterface $storeManager,
        FilterProvider $filterProvider,
        CollectionFactory $brandDetailsFactory,
        ProductUrl $productUrl,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->storeManager = $storeManager;
        $this->filterProvider = $filterProvider;
        $this->brandsFactory = $brandDetailsFactory;
        $this->productUrl = $productUrl;
        $this->categoryRepository = $categoryRepository;
        parent::__construct($context);
    }

    /**
     * @param string $field
     *
     * @return mixed
     */
    protected function getConfig($field)
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH . $field);
    }

    /**
     * @return mixed|string
     */
    public function getLandingPath()
    {
        $landingPath = $this->scopeConfig->getValue(self::ROUTES_PATH . self::ROUTES_LANDING);
        return !empty($landingPath) ? $landingPath : self::DEFAULT_LANDING;
    }

    /**
     * @return string[]
     */
    public function getModuleRoutes()
    {
        $landingPath = $this->getLandingPath();

        return [
            $landingPath => 'landing'
        ];
    }

    /**
     * @return string
     */
    public function getBrandAttributeCode()
    {
        return self::BRAND_ATTR_CODE;
    }

    /**
     * @return array
     */
    public function getConfigSliderBrands()
    {
        $optionIds = (string)$this->getconfig(self::CONFIG_SHOW_IN_SLIDER);

        return explode(',', $optionIds);
    }

    /**
     * @param mixed $categoryId
     *
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     * @throws NoSuchEntityException
     */
    protected function getCategoryById($categoryId)
    {
        $category = $this->categoryRepository->get($categoryId);

        return $category;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBrandIndexLink()
    {
        $linkType = (string)$this->getconfig(self::CONFIG_SLIDER_LINK_TYPE);
        $linkUrl = $this->getBaseUrl() . $this->getLandingPath();
        $catUrl = '';
        if ($linkType == 'category') {
            $catId = (int)$this->getconfig(self::CONFIG_SLIDER_LINK_CID);
            if (!empty($catId)) {
                $category = $this->getCategoryById($catId);
                $catUrl = is_object($category) ? $category->getUrl() : '';
            }
        }

        return !empty($catUrl) ? $catUrl : $linkUrl;
    }

    /**
     * @return ProductAttributeInterface
     * @throws NoSuchEntityException
     */
    public function getBrandAttribute()
    {
        if (!is_object($this->brandAttribute)) {
            $this->brandAttribute = $this->productAttributeRepository->get($this->getBrandAttributeCode());
        }

        return $this->brandAttribute;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getBrandOptions()
    {
        $brand = $this->getBrandAttribute();
        $options = $brand->getOptions();
        $values = [];
        foreach ($options as $option) {
            $values[$option->getValue()] = $option->getLabel();
        }

        return $values;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getActiveBrandOptions()
    {
        $brandList = $this->getBrandList();
        $values = [];
        foreach ($brandList as $option) {
            $values[$option->getOptionId()] = $option->getName();
        }

        return $values;
    }

    /**
     * @param $str
     *
     * @return string
     * @throws \Exception
     */
    public function filterCms($str)
    {
        // avoid null && false
        $str = !empty($str) ? $str : '';
        return $this->filterProvider->getPageFilter()->filter($str);
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getBaseMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @param BrandDetails $brand
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBrandUrl(BrandDetails $brand)
    {
        $brandUrl = $this->getBaseUrl() . $brand->getCategoryUrl();
        if ($brand->getUrlActiveCode() == 'landing') {
            $brandUrl = $this->getBaseUrl() . $this->getLandingPath() . '/' . $brand->getLandingUrlKey();
        }

        return $brandUrl;
    }

    /**
     * @param array $brandOptions
     * @param mixed $filterActive
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getBrandList($brandOptions = [], $filterActive = 1)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $collection = $this->brandsFactory->create();
        // select subset of url_rewrite data by store/entity_type
        $urlRewrite = "(select * from url_rewrite where entity_type = 'category' and store_id = $storeId)";
        // get active brands
        $collection->addFieldToSelect('*');
        if ($filterActive !== false) {
            $collection->addFieldToFilter('active', ['eq' => 1]);
        }
        if (!empty($brandOptions)) {
            foreach ($brandOptions as $field => $value) {
                // optional extra filter
                $collection->addFieldToFilter($field, ['eq' => $value]);
            }
        }
        // get url_rewrite data
        $collection->getSelect()
            ->joinLeft(
                ['u' => new Zend_Db_Expr($urlRewrite)],
                'main_table.category_id = u.entity_id AND u.target_path LIKE "catalog/category/view/%"',
                ['category_url' => 'request_path']
            )
            ->order('sort_order asc')
            ->order('name asc')
            //->__toString()
        ;

        return $collection;
    }

    /**
     * @param $brandOptionId
     *
     * @return array|mixed
     * @throws NoSuchEntityException
     */
    public function getBrandUrlDetails($brandOptionId)
    {
        if (empty($this->brandUrlDetails)) {
            foreach ($this->getBrandList() as $brand) {
                $this->brandUrlDetails[$brand->getOptionId()] = [
                    'active' => $brand->getActive(),
                    'name'   => $brand->getName(),
                    'logo'   => $this->getBaseMediaUrl() . $brand->getLogo(),
                    'url'    => $this->getBrandUrl($brand)
                ];
            }
        }

        return isset($this->brandUrlDetails[$brandOptionId]) ? $this->brandUrlDetails[$brandOptionId] : [];
    }

    public function updateBrandByOption($option)
    {
        $optionId = (int)$option['value'];
        $optionLabel = $option['label'];
        $optionUrl = $this->productUrl->formatUrlKey($optionLabel) .'-'.$optionId;
        if (!empty($optionId) && !empty($optionLabel)) {
            /**
             * @var $brandDetails \Ecommerce66\Brands\Model\BrandDetails
             */
            $brandDetails = $this->getBrandList(['option_id' => (int)$optionId], false)->getFirstItem();

            $brandDetails->setName($optionLabel);

            if (empty($brandDetails->getOptionId())) {
                $brandDetails->setOptionId($optionId);
            }

            if (empty($brandDetails->getLandingUrlKey())) {
                $brandDetails->setLandingUrlKey($optionUrl);
            }
            try {
                $brandDetails->save();
                //$this->brandDetailsRepository->save($brandDetails);
            } catch (LocalizedException $e) {
                $e->getMessage();
            }
        }
    }
}
