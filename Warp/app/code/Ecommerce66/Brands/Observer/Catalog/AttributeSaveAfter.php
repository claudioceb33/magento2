<?php
namespace Ecommerce66\Brands\Observer\Catalog;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Ecommerce66\Brands\Model\BrandDetailsFactory;
use Ecommerce66\Brands\Model\BrandDetailsRepository;
use Ecommerce66\Brands\Model\ResourceModel\BrandDetails\CollectionFactory as BrandDetailsCollectionFactory;
use Ecommerce66\Brands\Helper\Data as HelperData;
use Magento\Catalog\Model\Product\Url as ProductUrl;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Exception\NoSuchEntityException;
use \Exception;

class AttributeSaveAfter implements ObserverInterface
{
    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var BrandDetailsFactory
     */
    protected $brandDetailsFactory;

    /**
     * @var BrandDetailsRepository
     */
    protected $brandDetailsRepository;

    /**
     * @var BrandDetailsCollectionFactory
     */
    protected $brandDetailsCollectionFactory;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var ProductUrl
     */
    protected $productUrl;

    /**
     * @var EavConfig
     */
    protected $eavConfig;

    /**
     * AttributeSaveAfter constructor.
     *
     * @param AttributeRepositoryInterface  $attributeRepository
     * @param BrandDetailsFactory           $brandDetailsFactory
     * @param BrandDetailsRepository        $brandDetailsRepository
     * @param BrandDetailsCollectionFactory $brandDetailsCollectionFactory
     * @param HelperData                    $helperdata
     * @param ProductUrl                    $productUrl
     * @param EavConfig                     $eavConfig
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        BrandDetailsFactory $brandDetailsFactory,
        BrandDetailsRepository $brandDetailsRepository,
        BrandDetailsCollectionFactory $brandDetailsCollectionFactory,
        HelperData $helperdata,
        ProductUrl $productUrl,
        EavConfig $eavConfig
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->brandDetailsFactory = $brandDetailsFactory;
        $this->brandDetailsRepository = $brandDetailsRepository;
        $this->brandDetailsCollectionFactory = $brandDetailsCollectionFactory;
        $this->helperData = $helperdata;
        $this->productUrl = $productUrl;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param Observer $observer
     *
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        // check if the saved attribute is the brand attribute
        $postAttributeId = (int)$observer->getEvent()->getRequest()->getParam('attribute_id');
        $brandAttrCode = $this->helperData->getBrandAttributeCode();
        $brandAttribute = $this->eavConfig->getAttribute('catalog_product', $brandAttrCode);
        // compare attribute ids
        if ($postAttributeId == (int)$brandAttribute->getId()) {
            $options = $brandAttribute->getSource()->getAllOptions();
            try {
                if (!empty($options)) {
                    foreach ($options as $option) {
                        $this->helperData->updateBrandByOption($option);
                    }
                    $attrOptions = $brandAttribute->getOption();
                    $this->deleteOptions($attrOptions);
                }
            } catch (LocalizedException $e) {
                $e->getMessage();
            } catch (\Exception $e) {
                $e->getMessage();
            }
        }
    }

    /**
     * @param $options
     *
     * @throws NoSuchEntityException
     */
    protected function deleteOptions($options)
    {
        $deletes = isset($options['delete']) ? $options['delete'] : [];
        if (count($deletes)) {
            foreach ($deletes as $optionId => $value) {
                if ((int)$value == 1) {
                    $brandDetails = $this->helperData
                        ->getBrandList(['option_id' => (int)$optionId])
                        ->getFirstItem();
                    if (is_object($brandDetails) && $brandDetails->getId()) {
                        $brandDetails->delete();
                    }
                }
            }
        }
    }
}
