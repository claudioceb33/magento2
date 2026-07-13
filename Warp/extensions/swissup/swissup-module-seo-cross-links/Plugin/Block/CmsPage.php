<?php

namespace Swissup\SeoCrossLinks\Plugin\Block;

use Swissup\SeoCrossLinks\Helper\Data;
use Swissup\SeoCrossLinks\Model\Filter;
use Swissup\SeoCrossLinks\Model\Link;

class CmsPage
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Data $helper
     * @param Filter $filter
     */
    public function __construct(
        Data $helper,
        Filter $filter,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
        $this->filter = $filter;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $result
     * @return string
     */
    public function afterToHtml(\Magento\Cms\Block\Page $subject, $result)
    {
        if (!$this->helper->IsEnabled()) {
            return $result;
        }

        if (!empty($result) && is_string($result)) {
            $result = $this->filter
                ->setMode(Link::SEARCH_IN_CMS)
                ->setStoreId($this->storeManager->getStore()->getId())
                ->filter($result);
        }

        return $result;
    }
}
