<?php
declare(strict_types=1);

namespace Ecommerce66\Brands\Model;

use Magento\Framework\Model\AbstractModel;
use Ecommerce66\Brands\Api\Data\BrandDetailsInterface;

class BrandDetails extends AbstractModel implements BrandDetailsInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Ecommerce66\Brands\Model\ResourceModel\BrandDetails::class);
    }

    /**
     * @inheritDoc
     */
    public function getBrandDetailsId()
    {
        return $this->getData(self::BRAND_DETAILS_ID);
    }

    /**
     * @inheritDoc
     */
    public function setBrandDetailsId($brandDetailsId)
    {
        return $this->setData(self::BRAND_DETAILS_ID, $brandDetailsId);
    }

    /**
     * @inheritDoc
     */
    public function getActive()
    {
        return (int)$this->getData(self::ACTIVE);
    }

    /**
     * @inheritDoc
     */
    public function setActive($value)
    {
        return $this->setData(self::ACTIVE, $value);
    }

    /**
     * @inheritDoc
     */
    public function getCategoryId()
    {
        return $this->getData(self::CATEGORY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCategoryId($value)
    {
        return $this->setData(self::CATEGORY_ID, $value);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName($value)
    {
        return $this->setData(self::NAME, $value);
    }

    /**
     * @inheritDoc
     */
    public function getLogo()
    {
        return $this->getData(self::LOGO);
    }

    /**
     * @inheritDoc
     */
    public function setLogo($value)
    {
        return $this->setData(self::LOGO, $value);
    }

    /**
     * @inheritDoc
     */
    public function getImage()
    {
        return $this->getData(self::BRAND_IMAGE);
    }

    /**
     * @inheritDoc
     */
    public function setImage($value)
    {
        return $this->setData(self::BRAND_IMAGE, $value);
    }

    /**
     * @inheritDoc
     */
    public function getMobileImage()
    {
        return $this->getData(self::BRAND_MOB_IMG);
    }

    /**
     * @inheritDoc
     */
    public function setMobileImage($value)
    {
        return $this->setData(self::BRAND_MOB_IMG, $value);
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return $this->getData(self::BRAND_DESCRIPTION);
    }

    /**
     * @inheritDoc
     */
    public function setDescription($value)
    {
        return $this->setData(self::BRAND_DESCRIPTION, $value);
    }

    /**
     * @inheritDoc
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * @inheritDoc
     */
    public function setSortOrder($value)
    {
        return $this->setData(self::SORT_ORDER, $value);
    }

    /**
     * @inheritDoc
     */
    public function getOptionId()
    {
        return $this->getData(self::OPTION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOptionId($value)
    {
        return $this->setData(self::OPTION_ID, $value);
    }

    /**
     * @inheritDoc
     */
    public function getLandingActive()
    {
        return (int)$this->getData(self::LANDING_ACTIVE);
    }

    /**
     * @inheritDoc
     */
    public function setLandingActive($value)
    {
        return $this->setData(self::LANDING_ACTIVE, $value);
    }

    /**
     * @inheritDoc
     */
    public function getLandingName()
    {
        return $this->getData(self::LANDING_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setLandingName($value)
    {
        return $this->setData(self::LANDING_NAME, $value);
    }

    /**
     * @inheritDoc
     */
    public function getLandingUrlKey()
    {
        return $this->getData(self::LANDING_URL_KEY);
    }

    /**
     * @inheritDoc
     */
    public function setLandingUrlKey($value)
    {
        return $this->setData(self::LANDING_URL_KEY, $value);
    }

    /**
     * @inheritDoc
     */
    public function getLandingImage()
    {
        return $this->getData(self::LANDING_IMAGE);
    }

    /**
     * @inheritDoc
     */
    public function setLandingImage($value)
    {
        return $this->setData(self::LANDING_IMAGE, $value);
    }

    /**
     * @inheritDoc
     */
    public function getLandingMobileImage()
    {
        return $this->getData(self::LANDING_MOB_IMG);
    }

    /**
     * @inheritDoc
     */
    public function setLandingMobileImage($value)
    {
        return $this->setData(self::LANDING_MOB_IMG, $value);
    }

    /**
     * @inheritDoc
     */
    public function getLandingContent()
    {
        return $this->getData(self::LANDING_CONTENT);
    }

    /**
     * @inheritDoc
     */
    public function setLandingContent($value)
    {
        return $this->setData(self::LANDING_CONTENT, $value);
    }

    /**
     * @inheritDoc
     */
    public function getLandingMetaKey()
    {
        return $this->getData(self::LANDING_META_KEY);
    }

    /**
     * @inheritDoc
     */
    public function setLandingMetaKey($value)
    {
        return $this->setData(self::LANDING_META_KEY, $value);
    }

    /**
     * @inheritDoc
     */
    public function getLandingMetaDesc()
    {
        return $this->getData(self::LANDING_META_DESC);
    }

    /**
     * @inheritDoc
     */
    public function setLandingMetaDesc($value)
    {
        return $this->setData(self::LANDING_META_DESC, $value);
    }

    /**
     * @inheritDoc
     */
    public function getProductActive()
    {
        return $this->getData(self::PRODUCT_ACTIVE);
    }

    /**
     * @inheritDoc
     */
    public function setProductActive($value)
    {
        return $this->setData(self::PRODUCT_ACTIVE, $value);
    }

    /**
     * @inheritDoc
     */
    public function getProductBannerImg()
    {
        return $this->getData(self::PRODUCT_BANNER_IMG);
    }

    /**
     * @inheritDoc
     */
    public function setProductBannerImg($value)
    {
        return $this->setData(self::PRODUCT_BANNER_IMG, $value);
    }

    /**
     * @inheritDoc
     */
    public function getProductBannerBkg()
    {
        return $this->getData(self::PRODUCT_BANNER_BKG);
    }

    /**
     * @inheritDoc
     */
    public function setProductBannerBkg($value)
    {
        return $this->setData(self::PRODUCT_BANNER_BKG, $value);
    }

    /**
     * @inheritDoc
     */
    public function getProductBannerUrl()
    {
        return $this->getData(self::PRODUCT_BANNER_URL);
    }

    /**
     * @inheritDoc
     */
    public function setProductBannerUrl($value)
    {
        return $this->setData(self::PRODUCT_BANNER_URL, $value);
    }

    /**
     * @return string
     */
    public function getUrlActiveCode()
    {
        return (bool)$this->getLandingActive() && !empty($this->getLandingUrlKey()) ? 'landing' : 'category';
    }
}
