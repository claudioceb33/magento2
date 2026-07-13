<?php

namespace Swissup\SeoCrossLinks\Plugin\Megefan\Block;

use Swissup\SeoCrossLinks\Helper\Data;
use Swissup\SeoCrossLinks\Model\Filter;
use Swissup\SeoCrossLinks\Model\Link;

class PostList
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
     * @param Object $result
     */
    public function afterGetPostCollection(\Magefan\Blog\Block\Post\PostList\AbstractList $subject, $result)
    {
        if (!$this->helper->IsEnabled()) {
            return $result;
        }

        foreach ($result as $data) {
            $content = $data["content"];

            if (!empty($content) && is_string($content)) {
                $content = $this->filter
                    ->setMode(Link::SEARCH_IN_CMS)
                    ->setStoreId($this->storeManager->getStore()->getId())
                    ->filter($content);
                $data["content"] = $content;
            }
        }

        return $result;
    }
}
