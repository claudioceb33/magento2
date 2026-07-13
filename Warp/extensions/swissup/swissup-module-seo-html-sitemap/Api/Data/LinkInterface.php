<?php
namespace Swissup\SeoHtmlSitemap\Api\Data;

interface LinkInterface
{
    CONST LINK_ID = 'link_id';
    CONST STATUS = 'status';
    CONST NAME = 'name';
    CONST URL = 'url';
    CONST CREATION_TIME = 'creation_time';
    CONST UPDATE_TIME = 'update_time';

    /**
     * Get link_id
     *
     * return int
     */
    public function getLinkId();

    /**
     * Get status
     *
     * return int
     */
    public function getStatus();

    /**
     * Get name
     *
     * return string
     */
    public function getName();

    /**
     * Get url
     *
     * return string
     */
    public function getUrl();

    /**
     * Get creation_date
     *
     * return string
     */
    public function getCreationTime();

    /**
     * Get update_time
     *
     * return string
     */
    public function getUpdateTime();

    /**
     * Set link_id
     *
     * @param int $link_id
     * return LinkInterface
     */
    public function setLinkId($linkId);

    /**
     * Set status
     *
     * @param int $status
     * return LinkInterface
     */
    public function setStatus($status);

    /**
     * Set name
     *
     * @param string $name
     * return LinkInterface
     */
    public function setName($name);

    /**
     * Set url
     *
     * @param string $url
     * return LinkInterface
     */
    public function setUrl($url);

    /**
     * Set creation_time
     *
     * @param string $creationTime
     * return LinkInterface
     */
    public function setCreationTime($creationTime);

    /**
     * Set update_time
     *
     * @param string $updateTime
     * return LinkInterface
     */
    public function setUpdateTime($updateTime);
}
