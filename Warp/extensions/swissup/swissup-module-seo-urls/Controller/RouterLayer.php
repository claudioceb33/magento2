<?php

namespace Swissup\SeoUrls\Controller;

use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use Magento\UrlRewrite\Model\UrlFinderInterface;

/**
 * SeoUrls Controller Router for Layared Navigation
 */
class RouterLayer extends \Magento\UrlRewrite\Controller\Router
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
     * @var UrlRewriteCollectionFactory
     */
    protected $rewriteCollectionFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Swissup\SeoCore\Model\Url
     */
    protected $seoUrl;

    /**
     * @param UrlRewriteCollectionFactory $rewriteCollectionFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Swissup\SeoUrls\Helper\Data $helper
     * @param \Swissup\SeoUrls\Model\Request $seoRequest
     * @param \Swissup\SeoCore\Model\Url $seoUrl
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param UrlFinderInterface $urlFinder
     */
    public function __construct(
        UrlRewriteCollectionFactory $rewriteCollectionFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Swissup\SeoUrls\Helper\Data $helper,
        \Swissup\SeoUrls\Model\Request $seoRequest,
        \Swissup\SeoCore\Model\Url $seoUrl,
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResponseInterface $response,
        UrlFinderInterface $urlFinder
    ) {
        $this->rewriteCollectionFactory = $rewriteCollectionFactory;
        $this->eventManager = $eventManager;
        $this->helper = $helper;
        $this->seoRequest = $seoRequest;
        $this->seoUrl = $seoUrl;
        parent::__construct($actionFactory, $url, $storeManager, $response, $urlFinder);
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

        $separator = '/';
        if ($this->helper->isSeparateFilters()) {
            $separator .= $this->helper->getFiltersSeparator() . '/';
        }

        $pathInfo = $this->seoUrl->getBody(
            ltrim($request->getPathInfo(), '/'),
            true // exlude extension
        );
        $levels = explode($separator, $pathInfo);
        $collection = $this->rewriteCollectionFactory->create();
        $collection->addFieldToFilter(
            'target_path',
            ['like' => 'catalog/category/view%']
        );
        $collection->addFieldToFilter(
            'request_path',
            ['like' => reset($levels) . '%']
        );
        $collection->addStoreFilter(
            [$this->storeManager->getStore()->getId()],
            false
        );
        $collection->setOrder(
            'request_path',
            \Magento\Framework\Data\Collection::SORT_ORDER_DESC
        );

        $this->eventManager->dispatch(
            'swissup_seo_urls_prepare_rewrite_items',
            [
                'collection' => $collection->load(),
            ]
        );

        foreach ($collection as $rewrite) {
            $requestPathWithoutExt = $this->seoUrl->getBody(
                $rewrite->getRequestPath(),
                true // exclude extension
            );
            $requestPathWithoutExt = rtrim($requestPathWithoutExt, '/') . $separator;
            if (strpos($pathInfo, $requestPathWithoutExt) === 0) {
                if ($rewrite->getEntityType() == 'category') {
                    $categoryId = $rewrite->getEntityId();
                } else {
                    $categoryId = null;
                }

                $queryString = mb_substr($pathInfo, mb_strlen($requestPathWithoutExt));
                $queryParams = $this->seoRequest->getParamsFromString($queryString, (int)$categoryId);
                if (empty($queryParams)                     // no parameters
                    || isset($queryParams['seo_leftovers']) // there is something unrecognized
                ) {
                    continue;
                }

                $params = $this->seoRequest->mergeAndAppendValues(
                    $queryParams,
                    $request->getParams()
                );
                $request->setParams($params);
                if ($rewrite->getRedirectType()) {
                    return $this->processRedirect($request, $rewrite);
                }

                $request->setAlias(
                    \Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS,
                    $rewrite->getRequestPath()
                );
                $request->setPathInfo('/' . $rewrite->getTargetPath());
                return $this->actionFactory->create(
                    \Magento\Framework\App\Action\Forward::class
                );
            }
        }

        return null;
    }
}
