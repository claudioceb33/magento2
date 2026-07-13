<?php
/**
 * Plugin for class \Magento\Store\Block\Switcher
 */
namespace Swissup\Hreflang\Plugin\Block;

use \Magento\Store\Model\Store;

class Switcher extends \Swissup\Hreflang\Plugin\AbstractPlugin
{
    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $postDataHelper;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $store;

    /**
     * @var array
     */
    private $data;

    /**
     * Construct
     *
     * @param \Swissup\Hreflang\Helper\Store $helper
     */
    public function __construct(
        \Swissup\Hreflang\Helper\Store $helper,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper
    ){
        parent::__construct($helper);
        $this->urlHelper = $urlHelper;
        $this->postDataHelper = $postDataHelper;
    }

    /**
     * Save call arguments for after plugin. Compatibility with Magento 2.1.x.
     *
     * @param  \Magento\Store\Block\Switcher $subject
     * @param  Store                         $store
     * @param  array                         $data
     */
    public function beforeGetTargetStorePostData(
        \Magento\Store\Block\Switcher $subject,
        Store $store,
        $data = []
    ) {
        $this->store = $store; // Compatibility with Magento 2.1.x.
        $this->data = $data; // Compatibility with Magento 2.1.x.
        return null;
    }

    /**
     * After method 'getTargetStorePostData'.
     *
     * set proper redirect URL and do not add '___store' data parameter
     *
     * @param  \Magento\Store\Block\Switcher $subject
     * @param  string                        $result
     * @param  Store                         $store
     * @param  array                         $data
     * @return string
     */
    public function afterGetTargetStorePostData(
        \Magento\Store\Block\Switcher $subject,
        $result,
        Store $store = null ,
        $data = null
    ) {
        $store = $store ?: $this->store; // Compatibility with Magento 2.1.x.
        if ($this->helper->isLocaleInUrl($store)) {
            $data = $data ?: $this->data; // Compatibility with Magento 2.1.x.
            $storeCurrentUrl = $store->getCurrentUrl(true);
            $keyUenc = \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED;
            $data[$keyUenc] = $this->urlHelper->getEncodedUrl($storeCurrentUrl);
            $postData = $this->postDataHelper->getPostData(
                $storeCurrentUrl,
                $data
            );
        }

        return $result;
    }
}
