<?php

namespace Swissup\SeoCrossLinks\Plugin\Megefan\Block;

use Swissup\SeoCrossLinks\Helper\Data;
use Swissup\SeoCrossLinks\Model\Filter;
use Swissup\SeoCrossLinks\Model\Link;

class Description
{
    /**
     * @var \Swissup\SeoCrossLinks\Helper\Data
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
     * @param \Swissup\SeoCrossLinks\Helper\Data $helper
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
     * @param String $result
     */
    public function afterGetDescription(\Magefan\Blog\Block\Index\Description $subject, $result)
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
