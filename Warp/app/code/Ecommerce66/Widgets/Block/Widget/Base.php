<?php
declare(strict_types=1);

namespace Ecommerce66\Widgets\Block\Widget;

use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class Base extends Template implements BlockInterface
{
    /**
     * @var string
     */
    protected $baseMediaUrl;

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaUrl()
    {
        if (!$this->baseMediaUrl) {
            $this->baseMediaUrl = $this->_storeManager
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        }

        return $this->baseMediaUrl;
    }

    /**
     * @param $class
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @return mixed
     */
    public function getHelper($class)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // phpcs:ignore
        return $objectManager->get($class); // phpcs:ignore

    }

    /**
     * @param $dataKey
     *
     * @return mixed|string
     */
    public function getDataImage($image)
    {
        return $this->decodeImageUrl($image);
    }

    /**
     * @param $url
     *
     * @return mixed|string
     */
    protected function decodeImageUrl($url)
    {
        if (!empty($url)) {
            if (strpos($url, '/directive/___directive/') !== false) {
                $parts = explode('/', $url);
                $key = array_search("___directive", $parts);
                if ($key !== false) {
                    $url = $parts[$key + 1];
                    $url = base64_decode(strtr($url, '-_,', '+/=')); // phpcs:ignore
                }
            }

            if (strpos($url, '{{media url=') !== false) {
                $url = str_replace('&quot;', '', $url);
                $url = str_replace('"', '', $url);
                $parts = explode('=', $url);
                $key = array_search("{{media url=", $parts);
                $url = $parts[$key + 1];
            }

            $url = $this->getMediaUrl() . $url;
        }

        return $url;
    }
}
