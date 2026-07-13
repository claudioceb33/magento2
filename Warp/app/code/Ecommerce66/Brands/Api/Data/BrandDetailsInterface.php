<?php
declare(strict_types=1);

namespace Ecommerce66\Brands\Api\Data;

interface BrandDetailsInterface
{
    public const BRAND_DETAILS_ID   = 'brand_details_id';
    public const ACTIVE             = 'active';
    public const CATEGORY_ID        = 'category_id';
    public const NAME               = 'name';
    public const LOGO               = 'logo';
    public const BRAND_IMAGE        = 'image';
    public const BRAND_MOB_IMG      = 'mobile_image';
    public const BRAND_DESCRIPTION  = 'description';
    public const SORT_ORDER         = 'sort_order';
    public const OPTION_ID          = 'option_id';
    public const LANDING_ACTIVE     = 'landing_active';
    public const LANDING_NAME       = 'landing_name';
    public const LANDING_URL_KEY    = 'landing_url_key';
    public const LANDING_IMAGE      = 'landing_image';
    public const LANDING_MOB_IMG    = 'landing_mobile_image';
    public const LANDING_CONTENT    = 'landing_content';
    public const LANDING_META_KEY   = 'landing_meta_key';
    public const LANDING_META_DESC  = 'landing_meta_desc';
    public const PRODUCT_ACTIVE     = 'product_active';
    public const PRODUCT_BANNER_IMG = 'product_banner_img';
    public const PRODUCT_BANNER_BKG = 'product_banner_bkg';
    public const PRODUCT_BANNER_URL = 'product_banner_url';

    /**
     * Get brand_details_id
     * @return string|null
     */
    public function getBrandDetailsId();

    /**
     * Set brand_details_id
     * @param string $brandDetailsId
     * @return \Ecommerce66\Brands\BrandDetails\Api\Data\BrandDetailsInterface
     */
    public function setBrandDetailsId($brandDetailsId);

    /**
     * Get name
     * @return string|null
     */
    public function getActive();

    /**
     * Set name
     * @param string $value
     * @return \Ecommerce66\Brands\BrandDetails\Api\Data\BrandDetailsInterface
     */
    public function setActive($value);

    /**
     * Get name
     * @return string|null
     */
    public function getCategoryId();

    /**
     * Set name
     * @param string $value
     * @return \Ecommerce66\Brands\BrandDetails\Api\Data\BrandDetailsInterface
     */
    public function setCategoryId($value);

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $value
     * @return \Ecommerce66\Brands\BrandDetails\Api\Data\BrandDetailsInterface
     */
    public function setName($value);

    /**
     * Get name
     * @return string|null
     */
    public function getLogo();

    /**
     * Set name
     * @param string $value
     * @return \Ecommerce66\Brands\BrandDetails\Api\Data\BrandDetailsInterface
     */
    public function setLogo($value);

    /**
     * Get name
     * @return string|null
     */
    public function getImage();

    /**
     * Set name
     * @param string $value
     * @return \Ecommerce66\Brands\BrandDetails\Api\Data\BrandDetailsInterface
     */
    public function setImage($value);

    /**
     * Get name
     * @return string|null
     */
    public function getMobileImage();

    /**
     * Set name
     * @param string $value
     * @return \Ecommerce66\Brands\BrandDetails\Api\Data\BrandDetailsInterface
     */
    public function setMobileImage($value);

    /**
     * Get name
     * @return string|null
     */
    public function getDescription();

    /**
     * Set name
     * @param string $value
     * @return \Ecommerce66\Brands\BrandDetails\Api\Data\BrandDetailsInterface
     */
    public function setDescription($value);

    /**
     * Get name
     * @return string|null
     */
    public function getSortOrder();

    /**
     * Set name
     * @param string $value
     * @return \Ecommerce66\Brands\BrandDetails\Api\Data\BrandDetailsInterface
     */
    public function setSortOrder($value);

    /**
     * Get name
     * @return string|null
     */
    public function getOptionId();

    /**
     * Set name
     * @param string $value
     * @return \Ecommerce66\Brands\BrandDetails\Api\Data\BrandDetailsInterface
     */
    public function setOptionId($value);

    /**
     * Get name
     * @return string|null
     */
    public function getLandingActive();

    /**
     * Set name
     * @param string $value
     * @return \Ecommerce66\Brands\BrandDetails\Api\Data\BrandDetailsInterface
     */
    public function setLandingActive($value);

    /**
     * Get name
     * @return string|null
     */
    public function getLandingName();

    /**
     * Set name
     * @param string $value
     * @return \Ecommerce66\Brands\BrandDetails\Api\Data\BrandDetailsInterface
     */
    public function setLandingName($value);

    /**
     * Get name
     * @return string|null
     */
    public function getLandingUrlKey();

    /**
     * Set name
     * @param string $value
     * @return \Ecommerce66\Brands\BrandDetails\Api\Data\BrandDetailsInterface
     */
    public function setLandingUrlKey($value);

    /**
     * Get name
     * @return string|null
     */
    public function getLandingImage();

    /**
     * Set name
     * @param string $value
     * @return \Ecommerce66\Brands\BrandDetails\Api\Data\BrandDetailsInterface
     */
    public function setLandingImage($value);

    /**
     * Get name
     * @return string|null
     */
    public function getLandingMobileImage();

    /**
     * Set name
     * @param string $value
     * @return \Ecommerce66\Brands\BrandDetails\Api\Data\BrandDetailsInterface
     */
    public function setLandingMobileImage($value);

    /**
     * Get name
     * @return string|null
     */
    public function getLandingContent();

    /**
     * Set name
     * @param string $value
     * @return \Ecommerce66\Brands\BrandDetails\Api\Data\BrandDetailsInterface
     */
    public function setLandingContent($value);

    /**
     * Get name
     * @return string|null
     */
    public function getLandingMetaKey();

    /**
     * Set name
     * @param string $value
     * @return \Ecommerce66\Brands\BrandDetails\Api\Data\BrandDetailsInterface
     */
    public function setLandingMetaKey($value);

    /**
     * Get name
     * @return string|null
     */
    public function getLandingMetaDesc();

    /**
     * Set name
     * @param string $value
     * @return \Ecommerce66\Brands\BrandDetails\Api\Data\BrandDetailsInterface
     */
    public function setLandingMetaDesc($value);

    /**
     * Get name
     * @return string|null
     */
    public function getProductActive();

    /**
     * Set name
     * @param string $value
     * @return \Ecommerce66\Brands\BrandDetails\Api\Data\BrandDetailsInterface
     */
    public function setProductActive($value);

    /**
     * Get name
     * @return string|null
     */
    public function getProductBannerImg();

    /**
     * Set name
     * @param string $value
     * @return \Ecommerce66\Brands\BrandDetails\Api\Data\BrandDetailsInterface
     */
    public function setProductBannerImg($value);

    /**
     * Get name
     * @return string|null
     */
    public function getProductBannerBkg();

    /**
     * Set name
     * @param string $value
     * @return \Ecommerce66\Brands\BrandDetails\Api\Data\BrandDetailsInterface
     */
    public function setProductBannerBkg($value);

    /**
     * Get name
     * @return string|null
     */
    public function getProductBannerUrl();

    /**
     * Set name
     * @param string $value
     * @return \Ecommerce66\Brands\BrandDetails\Api\Data\BrandDetailsInterface
     */
    public function setProductBannerUrl($value);
}
