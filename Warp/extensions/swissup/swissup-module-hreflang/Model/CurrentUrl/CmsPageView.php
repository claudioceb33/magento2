<?php

namespace Swissup\Hreflang\Model\CurrentUrl;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\Store;
use Swissup\Hreflang\Model\ResourceModel\Page as PageResource;
use Swissup\SeoCore\Model\CurrentUrl\ProviderInterface;

class CmsPageView implements ProviderInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var PageInterface
     */
    protected $page;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var PageResource
     */
    protected $pageResource;

    /**
     * @param PageInterface           $page
     * @param PageRepositoryInterface $pageRepository
     * @param PageResource            $pageResource
     * @param RequestInterface        $request
     * @param SearchCriteriaBuilder   $searchCriteriaBuilder
     */
    public function __construct(
        PageInterface $page,
        PageRepositoryInterface $pageRepository,
        PageResource $pageResource,
        RequestInterface $request,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->page = $page;
        $this->pageRepository = $pageRepository;
        $this->pageResource = $pageResource;
        $this->request = $request;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function provide(
        Store $store,
        $queryParamsToUnset = []
    ) {
        $page = $this->page;
        $requestIdentifier = (string)$this->request->getAlias(
            \Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS
        );

        $hreflangIdentifier = $this->pageResource->getHreflangIdentifier($page, $store);
        if (!$hreflangIdentifier) {
            if ($requestIdentifier) {
                // Hraflang for page not assigned. Check if current identifier valid
                // for $store and use it.
                $pageId = $page->checkIdentifier($requestIdentifier,$store->getId());
                if (!$pageId) {
                    // cms page with such identifier not found
                    return null;
                }

                $hreflangIdentifier = $requestIdentifier;
            } else {
                // There is no request path alias. Seems like direct url used `cms/page/view/id`
                if (!in_array($store->getId(), $page->getStores())
                    && !in_array(Store::DEFAULT_STORE_ID, $page->getStores())
                ) {
                    return null;
                }
            }
        }

        $url = $store->getCurrentUrl(false, $queryParamsToUnset);
        return $requestIdentifier == $hreflangIdentifier
            ? $url
            : str_replace($requestIdentifier, $hreflangIdentifier, $url);
    }
}
