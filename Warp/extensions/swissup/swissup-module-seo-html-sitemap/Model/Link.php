<?php
namespace Swissup\SeoHtmlSitemap\Model;

use \Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;
use \Swissup\SeoHtmlSitemap\Api\Data\LinkInterface;

class Link extends AbstractModel implements LinkInterface, IdentityInterface
{

    const STATUS_DISABLED = 0;
    const STATUS_ENABLED  = 1;

    /**
     * cache tag
     */
    const CACHE_TAG = 'seohtmlsitemap_link';

    /**
     * @var string
     */
    protected $_cacheTag = 'seohtmlsitemap_link';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'seohtmlsitemap_link';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Swissup\SeoHtmlSitemap\Model\ResourceModel\Link');
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getLinkId()];
    }

    /**
     * Get link_id
     *
     * return int
     */
    public function getLinkId()
    {
        return $this->getData(self::LINK_ID);
    }

    /**
     * Get status
     *
     * return int
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Get name
     *
     * return string
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Get url
     *
     * return string
     */
    public function getUrl()
    {
        return $this->getData(self::URL);
    }

    /**
     * Get creation time
     *
     * return string
     */
    public function getCreationTime()
    {
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * Get update time
     *
     * return string
     */
    public function getUpdateTime()
    {
        return $this->getData(self::UPDATE_TIME);
    }

     /**
     * Prepare menu statuses
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED  => __('Enabled'),
            self::STATUS_DISABLED => __('Disabled')
        ];
    }

    /**
     * Set link_id
     *
     * @param int $link_id
     * return \Swissup\SeoHtmlSitemap\Api\Data\LinkInterface
     */
    public function setLinkId($link_id)
    {
        return $this->setData(self::LINK_ID, $link_id);
    }

    /**
     * Set status
     *
     * @param int $status
     * return \Swissup\SeoHtmlSitemap\Api\Data\LinkInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Set name
     *
     * @param string $name
     * return \Swissup\SeoHtmlSitemap\Api\Data\LinkInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Set url
     *
     * @param string $url
     * return \Swissup\SeoHtmlSitemap\Api\Data\LinkInterface
     */
    public function setUrl($url)
    {
        return $this->setData(self::URL, $url);
    }

    /**
     * Set creation time
     *
     * @param string $creation_time
     * return \Swissup\SeoHtmlSitemap\Api\Data\LinkInterface
     */
    public function setCreationTime($creation_time)
    {
        return $this->setData(self::CREATION_TIME, $creation_time);
    }

    /**
     * Set update time
     *
     * @param string $update_time
     * return \Swissup\SeoHtmlSitemap\Api\Data\LinkInterface
     */
    public function setUpdateTime($update_time)
    {
        return $this->setData(self::UPDATE_TIME, $update_time);
    }

    /**
     * Receive page store ids
     *
     * @return int[]
     */
    public function getStores()
    {
        return $this->hasData('stores') ? $this->getData('stores') : $this->getData('store_id');
    }
}
