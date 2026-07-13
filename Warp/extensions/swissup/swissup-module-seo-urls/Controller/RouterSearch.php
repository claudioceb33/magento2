<?php

namespace Swissup\SeoUrls\Controller;

/**
 * SeoUrls Controller Router for Search
 */
class RouterSearch implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Swissup\SeoUrls\Helper\Data
     */
    protected $helper;
    /**
     * @var \Swissup\SeoUrls\Model\Request
     */
    protected $seoRequest;
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;
    /**
     * @var  \Magento\Framework\UrlInterface
     */
    protected $url;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;
    /**
     * @var \Swissup\SeoCore\Model\Url
     */
    protected $seoUrl;

    /**
     * @param \Swissup\SeoUrls\Helper\Request $helper
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Swissup\SeoUrls\Helper\Data $helper,
        \Swissup\SeoUrls\Model\Request $seoRequest,
        \Swissup\SeoCore\Model\Url $seoUrl,
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Swissup\SeoUrls\Model\Url\Filter $url,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->helper = $helper;
        $this->seoRequest = $seoRequest;
        $this->seoUrl = $seoUrl;
        $this->actionFactory = $actionFactory;
        $this->url = $url;
        $this->storeManager = $storeManager;
        $this->response = $response;
    }

    /**
     * Match corresponding URL Rewrite with seo filters and modify request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|null
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->helper->isSeoUrlsEnabled()) {
            return null;
        }

        $pathInfo = ltrim($request->getPathInfo(), '/');
        $searchName = $this->helper->getSearchControllerName();
        $q = $request->getQueryValue('q', false);
        if ($q !== false && strpos($pathInfo, $searchName) === 0) {
            if ($this->helper->isSearchTermInUrl()) {
                // config search term place = 'in url'
                // redirect to new url
                return $this->redirectToSearchWithInUrlTerm($request);
            } else {
                // config search term place = 'as request value'
                // process request
                $queryString = str_replace($searchName, '', $pathInfo);
                return $this->forwardToCatalogSearch($request, $queryString, $searchName);
            }
        }

        // there is no request param 'q'; maybe search term in url enabled
        $searchName = $this->seoUrl->getBody($searchName, true); // without extension
        $searchName = rtrim($searchName, '/') . '/';
        $pathInfo = $this->seoUrl->getBody($pathInfo, true); // without extension
        if (strpos($pathInfo, $searchName) === 0) {
            // remove search name and get search term
            $queryString = str_replace($searchName, '', $pathInfo);
            $parts = explode('/', $queryString);
            $searchTerm = array_shift($parts);
            $alias = $this->seoUrl->rebuild(
                $this->helper->getSearchControllerName(),
                [$searchTerm]
            );
            $searchTerm = $this->convertSeoFriendlyToRegular($searchTerm);
            $request->setParam('q', $searchTerm);
            $queryString = implode('/', $parts);
            return $this->forwardToCatalogSearch($request, $queryString, $alias);
        }

        return null;
    }

    /**
     * Get normal string from seo friendly one
     *
     * @param  string $string
     * @return string
     */
    public function convertSeoFriendlyToRegular($string)
    {
        $string = urldecode($string);
        $regular = str_replace('-', ' ', $string);
        return trim($regular);
    }

    /**
     * Redirect to url with search term in it
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface
     */
    private function redirectToSearchWithInUrlTerm($request)
    {
        $name = $this->helper->getSearchControllerName();
        $query = $request->getQuery();
        $q = $this->helper->getSeoFriendlyString($query->get('q'));
        $query->set('q', null);
        $target = $this->seoUrl->rebuild($name, [$q]);
        if (!$query->get('cat')) { // cat=0
            $query->set('cat', null);
        }
        $url = $this->url->getUrl($target, ['_query' => $query->toArray()]);
        $this->response->setRedirect($url, 302);
        return $this->actionFactory->create(\Magento\Framework\App\Action\Redirect::class);
    }

    /**
     * Forward to catalog search result action and save alias
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $queryString
     * @param string $alias
     * @return \Magento\Framework\App\Action\Forward
     */
    private function forwardToCatalogSearch($request, $queryString, $alias)
    {
        $queryParams = $this->seoRequest->getParamsFromString(
            $queryString,
            (int)$this->storeManager->getStore()->getRootCategoryId()
        );
        if (isset($queryParams['seo_leftovers'])){ // there is something unrecognized
            return;
        }

        $params = $this->seoRequest->mergeAndAppendValues(
            $queryParams,
            $request->getParams()
        );
        $request->setParams($params);
        $request->setAlias(\Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $alias);
        $request->setPathInfo('/catalogsearch/result/index/');
        return $this->actionFactory->create(\Magento\Framework\App\Action\Forward::class);
    }
}
