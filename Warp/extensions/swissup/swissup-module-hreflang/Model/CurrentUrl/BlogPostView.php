<?php

namespace Swissup\Hreflang\Model\CurrentUrl;

use Swissup\SeoCore\Model\CurrentUrl\ProviderInterface;
use Magento\Framework\Url;

class BlogPostView implements ProviderInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * __construct
     *
     * @param \Magento\Framework\App\RequestInterface   $request
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function provide(
        \Magento\Store\Model\Store $store,
        $queryParamsToUnset = []
    ) {
        if (!class_exists('\Magefan\Blog\Model\Post')) {
            return null;
        }

        $currentBlogPost = $this->getCurrentBlogPost();
        if (!$currentBlogPost) {
            return null;
        }

        $postId = $currentBlogPost->checkIdentifier(
            $currentBlogPost->getIdentifier(),
            $store->getId()
        );

        if (!$postId) {
            // blog post with such identifier not found
            return null;
        }

        return $store->getCurrentUrl(false, $queryParamsToUnset);
    }

    private function getCurrentBlogPost() {
        return $this->objectManager
            ->get('\Magento\Framework\Registry')
            ->registry('current_blog_post');
    }
}
